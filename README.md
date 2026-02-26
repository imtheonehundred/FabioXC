# IPTV Panel (Laravel)

Laravel-based IPTV admin panel with **Player API** (Xtream Codes–compatible), **live restreaming** (FFmpeg HLS), **VOD**, **series**, **catch-up/timeshift**, and **reseller API**. Built with Laravel 12, Inertia.js, and Vue 3.

**Architecture:** This project follows the structure and engine described in [ARCHITECTURE.md](ARCHITECTURE.md) (domain, streaming, modules, lightweight streaming bootstrap). For a direct A–Z mapping of that doc to this repo, see **[ARCHITECTURE-COMPLIANCE.md](ARCHITECTURE-COMPLIANCE.md)**.

---

## Requirements

- **PHP** 8.2+ (cli, fpm, sqlite/mysql, mbstring, xml, curl, zip, bcmath)
- **Composer** 2.x
- **Node.js** 18+ and **npm** (for admin UI build)
- **FFmpeg** (for live restreaming)
- **Database**: SQLite (default) or MySQL/MariaDB

---

## Deploy on Ubuntu 22.04

See **[DEPLOY.md](DEPLOY.md)** for:

- Installing PHP, Nginx, FFmpeg, MySQL (optional)
- Clone, `.env`, `composer install`, `npm run build`, migrations
- Nginx config (including `/streaming/` for HLS)
- Cron for `schedule:run`, optional queue worker
- **Publish to GitHub** (create repo, then `git init` / `git push` from your machine)

---

## Local development

```bash
composer install
cp .env.example .env
php artisan key:generate
touch database/database.sqlite
php artisan migrate
npm install && npm run build
php artisan storage:link
php artisan serve
```

Admin panel: `http://localhost:8000`.  
Optional: `composer run dev` runs `php artisan serve`, queue, pail, and Vite together.

---

## Player API (customer app)

- **Base URL**: `http(s)://your-domain/player_api.php` (or your routed path).
- **Auth**: `username` + `password` (line credentials) as query params or basic auth.
- Endpoints: `get_live_streams`, `get_live_categories`, `get_vod_streams`, `get_vod_categories`, `get_series`, `get_series_categories`, `get_vod_info`, `get_short_vod_info`, etc.

### Catch-up (timeshift)

- `get_live_streams` includes `tv_archive` (0/1) and `tv_archive_duration` (hours).
- When `tv_archive === 1`, use:  
  `{base}/timeshift/{username}/{password}/{stream_id}?start={unix_start}&duration={seconds}`

### Fast on-demand open

- **`GET /api/player/get_on_demand_quick`** — returns `vod_categories`, `vod_streams`, `series_categories`, `series`, and `live_with_archive` in one call for quick UI load.

---

## Restreaming (outbound identity)

When the server restreams from external M3U/stream URLs, FFmpeg and any HTTP fetches use a configurable User-Agent so upstream providers are less likely to block you.

- **Config**: `config/streaming.php` — `outbound_user_agent` and optional `outbound_headers`.
- **Env**: `STREAMING_OUTBOUND_USER_AGENT=YourApp/1.0` in `.env` (optional).

---

## License

MIT.
