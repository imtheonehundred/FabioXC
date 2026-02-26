# ARCHITECTURE.md → This Project (A–Z)

This document maps **[ARCHITECTURE.md](ARCHITECTURE.md)** (XC_VM legacy migration plan) to **this Laravel IPTV project** and states what is implemented, what is different, and what is optional.

---

## 1. What ARCHITECTURE.md Is

ARCHITECTURE.md describes:

- A **legacy PHP codebase** (XC_VM): ~382 PHP files, god-objects (`CoreUtilities` 4847 lines, `admin_api.php` 6981 lines), duplicated bootstrap (`admin.php`, `stream/init.php`).
- A **target layout** under `/src`: `core/`, `domain/`, `streaming/`, `interfaces/`, `modules/`, `infrastructure/`, `data/`, `config/`, `resources/`.
- A **migration plan** (Phases 0–8): extract core → deduplicate → domain → streaming → modules → controllers → cleanup.

**This repo is not that legacy codebase.** It is a **Laravel** implementation that follows the same architectural ideas (domain, streaming, modules, light streaming bootstrap). The "engine" here is **Laravel + StreamingBootstrap + ModuleLoader**.

---

## 2. Mapping: ARCHITECTURE §3 (Target Structure) → This Project

| ARCHITECTURE.md (§3)        | This project                          | Status |
|----------------------------|----------------------------------------|--------|
| **src/bootstrap.php**     | Laravel `bootstrap/`, `public/index.php`, `public/stream.php` | ✅ Laravel bootstrap; streaming uses `StreamingBootstrap` |
| **src/constants.php**      | `config/*.php`, `.env`                 | ✅ |
| **src/core/**              | Laravel framework + `config/`         | ✅ Config, Database, Cache, Auth, Http, Process, Logging, etc. are Laravel |
| **src/domain/**            | `app/Domain/`                          | ✅ |
| **src/streaming/**         | `app/Streaming/`                       | ✅ |
| **src/interfaces/Http/**   | `app/Http/Controllers/`, `resources/js/` (Inertia/Vue) | ✅ |
| **src/interfaces/Cli/**    | `app/Console/Commands/`, `routes/console.php` | ✅ |
| **src/modules/**           | `app/Modules/`                         | ✅ |
| **src/infrastructure/**    | `app/Infrastructure/`, `infrastructure/` (README, nginx) | ✅ |
| **src/data/**              | `storage/` (logs, cache, streaming, content) | ✅ |
| **src/config/**            | `config/`                             | ✅ |
| **src/resources/**         | `resources/` (views, data, langs)     | ✅ |

---

## 3. Engine and Import (A–Z)

### 3.1 "Engine"

- **Core:** Laravel (config, DB, cache, auth, HTTP, queue, scheduler).
- **Streaming hot path:** `StreamingBootstrap` + `app/Streaming/*` (Auth, Delivery, Protection, Codec, Health, Balancer). No full Laravel bootstrap on streaming requests (see `public/stream.php` / streaming routes).
- **Modules:** `ModuleLoader` loads `config/modules.php`, runs `boot()`, `registerRoutes()`; event subscribers wired in `AppServiceProvider`; module crons wired in `routes/console.php`.

### 3.2 "Import"

- **M3U / playlists:** `App\Domain\Stream\M3UParser` (parse M3U). Admin UI: stream create/edit with source URL or upload.
- **VOD/Series:** Admin UI + optional Plex/TMDB modules for metadata.
- **EPG:** `domain/Epg` (EpgService, EpgRepository); EPG cron in `routes/console.php`.
- **Data resources:** `resources/data/` — `countries.php`, `timezones.php`, `mac_types.php`, `error_codes.php` (ARCHITECTURE §4.9, §5.3).

### 3.3 Domain (Controller → Service → Repository)

- **Stream:** StreamService, StreamRepository, StreamProcess, ConnectionTracker, PlaylistGenerator, CronGenerator, M3UParser, StreamMonitor.
- **VOD:** MovieService, MovieRepository; SeriesService; EpisodeService (EpisodeRepository via models).
- **Line:** LineService, LineRepository.
- **Server:** ServerService, ServerRepository.
- **User:** UserService, UserRepository.
- **Bouquet:** BouquetService, BouquetRepository.
- **Epg:** EpgService, EpgRepository.
- **Security:** BlocklistService, BlocklistRepository.
- **Device/Ticket:** Models and services as in ARCHITECTURE §3.

Controllers call Services/Repositories; no SQL in controllers.

### 3.4 Streaming

- **Auth:** TokenAuth, StreamAuth, DeviceLock.
- **Delivery:** LiveDelivery, VodDelivery, TimeshiftDelivery, SegmentReader.
- **Protection:** RestreamDetector, ConnectionLimiter, GeoBlock.
- **Codec:** FFmpegCommand, TranscodeProfile, TsParser.
- **Health:** DivergenceDetector, BitrateTracker.
- **Balancer:** LoadBalancer.
- **Bootstrap:** `StreamingBootstrap::boot()` (minimal config/DB for streaming).

### 3.5 Modules

All from ARCHITECTURE §4.5 / §5:

- **Ministra** — MinistraModule, PortalController.
- **Plex** — PlexModule, PlexController.
- **Tmdb** — TmdbModule, TmdbService.
- **Watch** — WatchModule, WatchController, RecordingService.
- **Fingerprint** — FingerprintModule.
- **TheftDetection** — TheftDetectionModule.
- **Magscan** — MagscanModule.

Each has `module.json` (or equivalent), implements `ModuleInterface` (`getName`, `getVersion`, `getDescription`, `boot`, `registerRoutes`, `registerCrons`, `getEventSubscribers`). Enabled in `config/modules.php`.

### 3.6 Infrastructure

- **Nginx:** `App\Infrastructure\Nginx\NginxConfigGenerator` (generate streaming snippet, reload).
- **Redis:** Laravel Redis facade (no separate RedisManager).
- **Daemons/processes:** FFmpeg started by `StreamProcess`; cron runs `schedule:run` and module crons.

### 3.7 Crons

- **Core crons** (in `routes/console.php`): streams, servers, cache, EPG, cleanup, backups, stats, logs, VOD.
- **Module crons:** Registered from `ModuleLoader::getAllCrons()` and scheduled in `routes/console.php` (see below).

### 3.8 MAIN vs LoadBalancer (ARCHITECTURE §8)

- **MAIN:** Full app (admin + streaming + modules) — this repo as-is.
- **LB:** Optional build that excludes admin-only parts. A **Makefile** is provided to produce an LB-friendly artifact (see Makefile and §8 below).

---

## 4. Implemented Checklist (A–Z)

| Item | Where | Done |
|------|--------|-----|
| Single bootstrap / streaming lightweight bootstrap | Laravel + `StreamingBootstrap` | ✅ |
| Core (Config, DB, Cache, Auth, Http, Process, Logging, Events, Container, Util) | Laravel + `app/Infrastructure` | ✅ |
| Domain (Stream, Vod, Line, Server, User, Bouquet, Epg, Security, Device, Ticket) | `app/Domain/` | ✅ |
| Streaming (Auth, Delivery, Protection, Codec, Health, Balancer) | `app/Streaming/` | ✅ |
| Controller → Service → Repository | Controllers use Services/Repos | ✅ |
| Modules (Ministra, Plex, Tmdb, Watch, Fingerprint, TheftDetection, Magscan) | `app/Modules/` + `config/modules.php` | ✅ |
| Module boot, routes, event subscribers | `ModuleLoader`, `AppServiceProvider` | ✅ |
| Module crons | `routes/console.php` + `ModuleLoader::getAllCrons()` | ✅ |
| Resources data (countries, timezones, mac_types, error_codes) | `resources/data/` | ✅ |
| Nginx generator | `app/Infrastructure/Nginx/NginxConfigGenerator` | ✅ |
| LB build option | Makefile (see §8) | ✅ |

---

## 5. Optional / Different

- **No `src/` root:** Uses Laravel's `app/`; structure is equivalent to ARCHITECTURE's `src/` layout.
- **No separate `core/` package:** Laravel provides core; no duplication.
- **Event system:** Custom `EventDispatcher` + module subscribers; Laravel events can be used in addition.
- **LB build:** Optional; used only when deploying a streaming-only node.

---

## 6. Module Crons Wiring

Module crons returned by `registerCrons()` are registered with Laravel's scheduler in `routes/console.php`:

- Each entry: `['schedule' => 'hourly'|'daily'|…, 'command' => 'artisan:command', 'description' => '…']`.
- Scheduler runs `php artisan schedule:run` every minute; module crons run at the defined frequency.

---

## 7. How to Extend (ARCHITECTURE §12)

- **New endpoint:** Add method in the right Domain Service/Repository, then in Controller or API.
- **New table:** New migration; new or extended Repository in `app/Domain/...`.
- **New module:** New dir under `app/Modules/`, implement `ModuleInterface`, add to `config/modules.php`.
- **Streaming:** Avoid heavy bootstrap and admin-only code in streaming routes; keep hot path under the latency budget (§10.6).

---

## 8. LB Build (ARCHITECTURE §8)

For a **streaming-only (LB)** node:

- Use the provided **Makefile** target `lb` (or equivalent) to copy only the needed app files (e.g. `app/Streaming/`, `app/Domain/Stream/`, `app/Domain/Server/`, minimal `app/Domain/` for delivery, `public/stream.php`, config, storage layout). Exclude admin UI, reseller, and admin-only modules.
- Deploy that artifact to the LB server; run only streaming entrypoint and crons that support streaming (e.g. stream health). See [DEPLOY.md](DEPLOY.md) for single-node; for LB, use the same steps on a minimal copy produced by the Makefile.

---

**Summary:** This project implements the architecture described in ARCHITECTURE.md in Laravel terms: engine (Laravel + StreamingBootstrap + ModuleLoader), domain/streaming/modules, import (M3U, VOD, EPG, resources/data), and optional LB build. Nothing from ARCHITECTURE.md is "missing" for the MAIN build; differences are naming (`app/` vs `src/`) and use of Laravel as the core layer.
