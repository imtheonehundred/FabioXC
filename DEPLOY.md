# Deploy on Ubuntu 22.04

This guide deploys the IPTV panel (Laravel + Inertia/Vue) on **Ubuntu 22.04** with PHP 8.2+, Nginx, and FFmpeg for restreaming.

## 1. System requirements

- **PHP** 8.2+ (cli, fpm, and extensions below)
- **Composer** 2.x
- **Node.js** 18+ and **npm**
- **FFmpeg** (for live restreaming)
- **Database**: SQLite (default) or MySQL/MariaDB
- **Nginx** (or Apache)

### Install dependencies (Ubuntu 22.04)

```bash
sudo apt update
sudo apt install -y php8.2-fpm php8.2-cli php8.2-sqlite3 php8.2-mysql php8.2-mbstring php8.2-xml php8.2-curl php8.2-zip php8.2-bcmath php8.2-intl unzip nginx ffmpeg
```

For **MySQL** instead of SQLite:

```bash
sudo apt install -y mysql-server php8.2-mysql
sudo mysql -e "CREATE DATABASE iptv CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci; CREATE USER 'iptv'@'localhost' IDENTIFIED BY 'YOUR_PASSWORD'; GRANT ALL ON iptv.* TO 'iptv'@'localhost'; FLUSH PRIVILEGES;"
```

Install **Composer** and **Node.js** if not present:

```bash
# Composer
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer

# Node.js 20 LTS (optional: use nvm instead)
curl -fsSL https://deb.nodesource.com/setup_20.x | sudo -E bash -
sudo apt install -y nodejs
```

---

## 2. Clone and install app

```bash
cd /var/www
sudo git clone https://github.com/YOUR_USER/iptv.git
sudo chown -R www-data:www-data iptv
cd iptv
```

Create `.env` from example and set production values:

```bash
cp .env.example .env
nano .env
```

Set at least:

- `APP_ENV=production`
- `APP_DEBUG=false`
- `APP_URL=https://your-domain.com` (or `http://your-server-ip`)
- For MySQL: `DB_CONNECTION=mysql`, `DB_DATABASE=iptv`, `DB_USERNAME=iptv`, `DB_PASSWORD=...`
- Optional: `STREAMING_OUTBOUND_USER_AGENT=...` for restreaming (see README)

Install PHP and frontend dependencies and build:

```bash
composer install --no-dev --optimize-autoloader
php artisan key:generate
php artisan migrate --force
npm ci
npm run build
php artisan storage:link
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

---

## 3. Permissions

```bash
sudo chown -R www-data:www-data /var/www/iptv
sudo chmod -R 755 /var/www/iptv
sudo chmod -R 775 /var/www/iptv/storage /var/www/iptv/bootstrap/cache
```

---

## 4. Nginx

Create a site config, e.g. `/etc/nginx/sites-available/iptv`:

```nginx
server {
    listen 80;
    server_name your-domain.com;   # or your server IP
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

    # Streaming: long-lived HLS requests
    location /streaming/ {
        try_files $uri $uri/ /index.php?$query_string;
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root/index.php;
        include fastcgi_params;
        fastcgi_read_timeout 600;
        fastcgi_buffering off;
    }
}
```

Enable and reload:

```bash
sudo ln -s /etc/nginx/sites-available/iptv /etc/nginx/sites-enabled/
sudo nginx -t && sudo systemctl reload nginx
```

For **HTTPS**, use Certbot:

```bash
sudo apt install certbot python3-certbot-nginx
sudo certbot --nginx -d your-domain.com
```

---

## 5. Cron (Laravel scheduler)

Laravel must run the scheduler every minute. Edit crontab for `www-data`:

```bash
sudo crontab -u www-data -e
```

Add:

```
* * * * * cd /var/www/iptv && php artisan schedule:run >> /dev/null 2>&1
```

---

## 6. Optional: queue worker

If you use queues (e.g. for jobs), run a worker:

```bash
sudo nano /etc/systemd/system/iptv-worker.service
```

```ini
[Unit]
Description=IPTV Laravel Queue Worker
After=network.target

[Service]
User=www-data
Group=www-data
Restart=always
ExecStart=/usr/bin/php /var/www/iptv/artisan queue:work --sleep=3 --tries=3

[Install]
WantedBy=multi-user.target
```

```bash
sudo systemctl daemon-reload
sudo systemctl enable iptv-worker
sudo systemctl start iptv-worker
```

---

## 7. Streaming (FFmpeg)

- Live restreaming runs FFmpeg processes started by the app (Admin → Streams → Start stream).
- HLS segments are written under `storage/app/streaming/<stream_id>/`.
- Ensure `storage` is writable by the web server (`www-data`).
- Optional: set `STREAMING_OUTBOUND_USER_AGENT` in `.env` so upstream providers see a normal client (see README).

---

## 8. First run

1. Open `https://your-domain.com` (or `http://your-ip`).
2. Run the database seeder if you need default admin/servers/lines:

   ```bash
   cd /var/www/iptv && php artisan db:seed --force
   ```

3. Log in with the seeded admin user (see `database/seeders/DatabaseSeeder.php` for credentials), then change the password and configure servers/lines/streams.

---

## 9. Updates from Git

```bash
cd /var/www/iptv
sudo -u www-data git pull
composer install --no-dev --optimize-autoloader
npm ci && npm run build
php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache
sudo systemctl restart iptv-worker   # if using queue worker
```

---

## Publish this project to GitHub

From your local machine (not the server):

```bash
cd /path/to/iptv
git init
git add .
git commit -m "Initial commit: IPTV panel ready for deploy"
git branch -M main
git remote add origin https://github.com/YOUR_USERNAME/iptv.git
git push -u origin main
```

Replace `YOUR_USERNAME` with your GitHub username. Create the `iptv` repository on GitHub first (empty, no README). Then run the commands above. For the server deploy (step 2), use your repo URL in `git clone`.
