<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

<p align="center">
<a href="https://github.com/laravel/framework/actions"><img src="https://github.com/laravel/framework/workflows/tests/badge.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>
</p>

## About Laravel

Laravel is a web application framework with expressive, elegant syntax. We believe development must be an enjoyable and creative experience to be truly fulfilling. Laravel takes the pain out of development by easing common tasks used in many web projects, such as:

- [Simple, fast routing engine](https://laravel.com/docs/routing).
- [Powerful dependency injection container](https://laravel.com/docs/container).
- Multiple back-ends for [session](https://laravel.com/docs/session) and [cache](https://laravel.com/docs/cache) storage.
- Expressive, intuitive [database ORM](https://laravel.com/docs/eloquent).
- Database agnostic [schema migrations](https://laravel.com/docs/migrations).
- [Robust background job processing](https://laravel.com/docs/queues).
- [Real-time event broadcasting](https://laravel.com/docs/broadcasting).

Laravel is accessible, powerful, and provides tools required for large, robust applications.

## Learning Laravel

Laravel has the most extensive and thorough [documentation](https://laravel.com/docs) and video tutorial library of all modern web application frameworks, making it a breeze to get started with the framework. You can also check out [Laravel Learn](https://laravel.com/learn), where you will be guided through building a modern Laravel application.

If you don't feel like reading, [Laracasts](https://laracasts.com) can help. Laracasts contains thousands of video tutorials on a range of topics including Laravel, modern PHP, unit testing, and JavaScript. Boost your skills by digging into our comprehensive video library.

## Laravel Sponsors

We would like to extend our thanks to the following sponsors for funding Laravel development. If you are interested in becoming a sponsor, please visit the [Laravel Partners program](https://partners.laravel.com).

### Premium Partners

- **[Vehikl](https://vehikl.com)**
- **[Tighten Co.](https://tighten.co)**
- **[Kirschbaum Development Group](https://kirschbaumdevelopment.com)**
- **[64 Robots](https://64robots.com)**
- **[Curotec](https://www.curotec.com/services/technologies/laravel)**
- **[DevSquad](https://devsquad.com/hire-laravel-developers)**
- **[Redberry](https://redberry.international/laravel-development)**
- **[Active Logic](https://activelogic.com)**

## Contributing

Thank you for considering contributing to the Laravel framework! The contribution guide can be found in the [Laravel documentation](https://laravel.com/docs/contributions).

## Code of Conduct

In order to ensure that the Laravel community is welcoming to all, please review and abide by the [Code of Conduct](https://laravel.com/docs/contributions#code-of-conduct).

## Security Vulnerabilities

If you discover a security vulnerability within Laravel, please send an e-mail to Taylor Otwell via [taylor@laravel.com](mailto:taylor@laravel.com). All security vulnerabilities will be promptly addressed.

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).

---

## Player API — On-demand and catch-up (customer app)

### Per-channel catch-up (timeshift)

- **`get_live_streams`** returns per stream:
  - `tv_archive` — `1` when catch-up is enabled, `0` otherwise.
  - `tv_archive_duration` — archive window in **hours** (e.g. 24, 48, 72).

When `tv_archive === 1`, the app can show “Catch-up” / “Watch from start” and use the timeshift URL:

```
{base}/timeshift/{username}/{password}/{stream_id}?start={unix_start}&duration={seconds}
```

- `start` — Unix timestamp of the desired start time.
- `duration` — length in seconds (e.g. `tv_archive_duration * 3600` for full window).

### Fast on-demand open

- **`GET /api/player/get_on_demand_quick`** — single request to open the on-demand section with minimal round-trips.

Returns one JSON object with:

- `vod_categories` — same shape as `get_vod_categories`.
- `vod_streams` — lightweight VOD list (e.g. id, name, stream_id, category_id, container_extension, stream_icon), limited (default 100, max 200 via `?limit=`).
- `series_categories` — same as `get_series_categories`.
- `series` — lightweight series list (e.g. id, name, series_id, category_id, cover), same limit.
- `live_with_archive` — list of channels with catch-up enabled (stream_id, name, tv_archive, tv_archive_duration).

**Usage:** Call `get_on_demand_quick` once when the user opens the on-demand tab; use the returned categories and lists for first paint. Optionally lazy-load full lists (`get_vod_streams`, `get_series`) when the user drills down.

Responses may be cached briefly (e.g. `Cache-Control: public, max-age=60`); tune `max-age` to your update frequency.

---

## Restreaming from external URLs (avoid upstream blocking)

When the server restreams channels from external M3U/stream URLs, outbound requests (e.g. FFmpeg pulling the source) use a configurable "normal user" identity so upstream IPTV providers are less likely to block your server. Many providers block typical server User-Agents (Lavf/FFmpeg, VLC, wget, etc.).

- **User-Agent and headers** are set in `config/streaming.php`: `outbound_user_agent` (default: consumer-like) and optional `outbound_headers`. You can override the User-Agent via `.env`: `STREAMING_OUTBOUND_USER_AGENT=YourApp/1.0`.
- **FFmpeg** uses these when the live stream source is an `http://` or `https://` URL.
- For any future PHP-based fetch of external M3U/stream URLs, use `Http::withHeaders(['User-Agent' => config('streaming.outbound_user_agent')])` and merge `config('streaming.outbound_headers')` so the same identity is sent.
