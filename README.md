# Sharewire

A small music-sharing app. Users upload MP3s to a shared catalog, add tracks to a personal library, and listen in the browser. Built as a portfolio piece for a Laravel 13 / Vue 3 interview on a 2.5-day deadline.

**Live demo:** <https://sharewire.taila21774.ts.net>

## What this demonstrates

The feature set is intentionally small (auth, upload, browse, search, add, play, remove, uploader-delete). The work to look at is the layering — every write endpoint flows through a Form Request, an Action class with a typed DTO, and a Resource on the way out.

- **Skinny controllers** (every method ≤ 15 lines), **Form Requests** with populated `rules()` and `authorize()`, **Actions** for every business operation, **Resources** for every JSON shape, **Policies** for every authorization decision.
- **Eloquent first** — zero `DB::raw` or `DB::table` in `app/`. Search is a `where` chain across four columns.
- **Pest** end-to-end: 120 tests, 419 assertions, ~2 seconds. Every controller endpoint has happy-path, unauthorized, and validation-failure coverage; every Action and Policy has a direct test.
- **Queued job** (`ProcessUploadedSong`) for audio-metadata extraction. The upload controller dispatches and redirects immediately; a systemd worker on the laptop fills in `duration_seconds` once `getID3` has parsed the file.
- **Inertia v3 + Vue 3 Composition API** with Wayfinder-generated typed routes — no hand-written URL strings in any `<Link>` or `<Form>`.
- **Module-level singleton composable** drives the global audio player so it survives Inertia page navigations. Pinia is intentionally not used.

Two choices that look like omissions but were deliberate, both noted in code comments:

- `spatie/laravel-data` has no Laravel 13 release as of this build, so DTOs in `app/Data/` are hand-rolled `final readonly` classes. Same shape, drop-in swap when Spatie ships.
- Audio is served behind session-cookie auth + `SongPolicy::play`, not signed URLs. Signed URLs are a one-line change in `SongResource` when more flexibility is needed.

## Architecture

A single Debian laptop runs the whole stack. Caddy terminates HTTP on `:8080` and reverse-proxies PHP requests to a PHP-FPM 8.4 Unix socket. Laravel writes to a SQLite file on local disk; uploaded MP3s live alongside it under `storage/app/private/songs/`. A systemd unit runs `php artisan queue:work`. Tailscale Funnel publishes `:443` to the public internet, terminating TLS in front of Caddy. The same code runs identically on a developer's macOS Herd setup — only `.env` differs.

## Setup

```bash
git clone https://github.com/Taylor-Rem/sharewire.git
cd sharewire
composer install
npm install && npm run build
cp .env.example .env
php artisan key:generate
touch database/database.sqlite
php artisan migrate
php artisan serve
```

Open `http://localhost:8000`, register, upload a copyright-free MP3, listen.

## Stack

PHP 8.4 · Laravel 13 · Inertia v3 · Vue 3.5 · TypeScript · Tailwind 4 · Vite 8 · Pest 4 · Pint · Wayfinder · Fortify · SQLite · Caddy · Tailscale Funnel

## Future work

Signed audio URLs · Larastan + GitHub Actions CI · FTS5 search at scale · REST API with Sanctum tokens for third-party clients · multi-user playlists.
