# Install on Ubuntu (quick guide)

Full details: [DEPLOY.md](DEPLOY.md). Below is a minimal path to get the panel running on **Ubuntu 22.04**.

---

## 1. Install PHP, Nginx, Node, FFmpeg, Git

```bash
sudo apt update
sudo apt install -y php8.2-fpm php8.2-cli php8.2-sqlite3 php8.2-mbstring php8.2-xml php8.2-curl php8.2-zip php8.2-bcmath php8.2-intl unzip nginx ffmpeg git curl
```

If PHP 8.2 is not available, use your distro’s PHP (e.g. `php-fpm php-cli php-sqlite3 ...`).

**Composer:**

```bash
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer
```

**Node.js 20 (optional, for building the admin UI):**

```bash
curl -fsSL https://deb.nodesource.com/setup_20.x | sudo -E bash -
sudo apt install -y nodejs
```

---

## 2. Clone the project

```bash
sudo mkdir -p /var/www
cd /var/www
sudo git clone https://github.com/imtheonehundred/FabioXC.git iptv
sudo chown -R www-data:www-data iptv
cd iptv
```

---

## 3. Configure environment

```bash
cp .env.example .env
nano .env
```

Set at least:

- `APP_ENV=production`
- `APP_DEBUG=false`
- `APP_URL=http://YOUR_SERVER_IP` or `https://your-domain.com`
- For MySQL instead of SQLite: set `DB_CONNECTION=mysql`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD` (and install `php8.2-mysql` + create the database).

---

## 4. Install dependencies and run migrations

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

## 5. Permissions

```bash
sudo chown -R www-data:www-data /var/www/iptv
sudo chmod -R 755 /var/www/iptv
sudo chmod -R 775 /var/www/iptv/storage /var/www/iptv/bootstrap/cache
```

---

## 6. Nginx

Create a site, e.g. `/etc/nginx/sites-available/iptv`:

```nginx
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
```

If your PHP-FPM socket is different (e.g. `php-fpm.sock`), change the `fastcgi_pass` line.

Enable and reload:

```bash
sudo ln -sf /etc/nginx/sites-available/iptv /etc/nginx/sites-enabled/
sudo rm -f /etc/nginx/sites-enabled/default
sudo nginx -t && sudo systemctl reload nginx
```

---

## 7. Cron (Laravel scheduler)

Run the scheduler every minute:

```bash
sudo crontab -u www-data -e
```

Add this line:

```
* * * * * cd /var/www/iptv && php artisan schedule:run >> /dev/null 2>&1
```

---

## 8. First login

1. Open **http://YOUR_SERVER_IP** in a browser.
2. If you need a default admin user and data, run (once):

   ```bash
   cd /var/www/iptv && php artisan db:seed --force
   ```

3. Log in with the credentials from `database/seeders/DatabaseSeeder.php` and change the password in the panel.

---

## Quick reference

| Step              | Command / action |
|-------------------|------------------|
| Install stack     | `apt install php8.2-fpm php8.2-cli php8.2-sqlite3 ... nginx ffmpeg git` |
| Composer          | `curl -sS https://getcomposer.org/installer \| php` then `sudo mv composer.phar /usr/local/bin/composer` |
| Clone             | `git clone https://github.com/imtheonehundred/FabioXC.git /var/www/iptv` |
| Env               | `cp .env.example .env` then edit `APP_URL`, `APP_ENV=production`, `APP_DEBUG=false` |
| Key               | `php artisan key:generate` |
| Migrate           | `php artisan migrate --force` |
| Frontend          | `npm ci && npm run build` |
| Storage link      | `php artisan storage:link` |
| Cache             | `php artisan config:cache && php artisan route:cache && php artisan view:cache` |
| Permissions       | `chown -R www-data:www-data /var/www/iptv` and `chmod -R 775 storage bootstrap/cache` |
| Nginx             | Site config with `root /var/www/iptv/public` and PHP-FPM, then `nginx -t && systemctl reload nginx` |
| Cron              | `* * * * * cd /var/www/iptv && php artisan schedule:run >> /dev/null 2>&1` for user `www-data` |

For HTTPS, MySQL, queue workers, and updates, see [DEPLOY.md](DEPLOY.md).
