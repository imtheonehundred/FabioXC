#!/bin/bash
# Run on VPS as root. Deploys FabioXC (IPTV panel) to /var/www/iptv
set -e
REPO_URL="https://github.com/imtheonehundred/FabioXC.git"
APP_DIR="/var/www/iptv"

echo "=== Updating system and installing dependencies ==="
export DEBIAN_FRONTEND=noninteractive
apt-get update -qq
apt-get install -y -qq php8.2-fpm php8.2-cli php8.2-sqlite3 php8.2-mbstring php8.2-xml php8.2-curl php8.2-zip php8.2-bcmath php8.2-intl unzip nginx ffmpeg git curl 2>/dev/null || {
  echo "Trying php (default)..."
  apt-get install -y -qq php-fpm php-cli php-sqlite3 php-mbstring php-xml php-curl php-zip php-bcmath php-intl unzip nginx ffmpeg git curl
}

if ! command -v composer &>/dev/null; then
  echo "=== Installing Composer ==="
  curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
fi

if ! command -v node &>/dev/null; then
  echo "=== Installing Node.js 20 ==="
  curl -fsSL https://deb.nodesource.com/setup_20.x | bash -
  apt-get install -y -qq nodejs
fi

echo "=== Clone or pull app ==="
if [ -d "$APP_DIR/.git" ]; then
  cd "$APP_DIR" && git pull -q && cd -
else
  rm -rf "$APP_DIR"
  git clone -q "$REPO_URL" "$APP_DIR"
fi
cd "$APP_DIR"

echo "=== Laravel setup ==="
if [ ! -f .env ]; then
  cp .env.example .env
  sed -i 's/APP_ENV=local/APP_ENV=production/' .env
  sed -i 's/APP_DEBUG=true/APP_DEBUG=false/' .env
  sed -i "s|APP_URL=.*|APP_URL=http://155.103.70.187|" .env
fi
php artisan key:generate --force
php artisan migrate --force
composer install --no-dev --optimize-autoloader --no-interaction
npm ci
npm run build
php artisan storage:link 2>/dev/null || true
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo "=== Permissions ==="
chown -R www-data:www-data "$APP_DIR"
chmod -R 755 "$APP_DIR"
chmod -R 775 "$APP_DIR/storage" "$APP_DIR/bootstrap/cache"

echo "=== Nginx ==="
cat > /etc/nginx/sites-available/iptv << 'NGINX'
server {
    listen 80 default_server;
    server_name _;
    root /var/www/iptv/public;
    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";
    index index.php;
    charset utf-8;
    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }
    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }
    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
        fastcgi_hide_header X-Powered-By;
        fastcgi_read_timeout 300;
    }
    location /streaming/ {
        try_files $uri $uri/ /index.php?$query_string;
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root/index.php;
        include fastcgi_params;
        fastcgi_read_timeout 600;
        fastcgi_buffering off;
    }
}
NGINX
# Fallback if PHP 8.2 socket doesn't exist
if [ ! -S /var/run/php/php8.2-fpm.sock ]; then
  PHP_SOCK=$(ls /var/run/php/php*-fpm.sock 2>/dev/null | head -1)
  if [ -n "$PHP_SOCK" ]; then
    sed -i "s|php8.2-fpm.sock|$(basename $PHP_SOCK)|g" /etc/nginx/sites-available/iptv
  fi
fi
ln -sf /etc/nginx/sites-available/iptv /etc/nginx/sites-enabled/
rm -f /etc/nginx/sites-enabled/default
nginx -t && systemctl reload nginx

echo "=== Cron (scheduler) ==="
(crontab -u www-data -l 2>/dev/null | grep -v "schedule:run"; echo "* * * * * cd $APP_DIR && php artisan schedule:run >> /dev/null 2>&1") | crontab -u www-data -

echo "=== Done. App: http://155.103.70.187 ==="
