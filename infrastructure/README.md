# Infrastructure

System-level components (ARCHITECTURE.md §4.6):

- **nginx/** — Nginx config generation and reload (multi-node / LB).
- **redis/** — Redis connection manager (optional; app uses Laravel Redis facade).
- **service/** — Process manager scripts (e.g. `service start/stop`) for daemons.
- **install/** — Initial DB schema or install assets (optional; app uses migrations).
- **bin/** — External binaries (FFmpeg, certbot, etc.) — usually provided by OS or deployment.

For a single-node deployment, Laravel + system nginx/Redis is sufficient. Use this layer when you need app-driven nginx generation or LB builds (§8).
