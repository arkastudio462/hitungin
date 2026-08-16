# Agent Guide

## Project

Laravel 13 + Vue 3 SPA — financial tracking app (PHP 8.3+, SQLite, Tailwind v4, Sanctum, Vite).

## Quick Start

```bash
composer setup
```
Copies `.env.example` to `.env`, generates app key, runs migrations + seeders, installs npm deps, builds assets.

## Dev Server

```bash
composer dev
```
Starts four concurrent processes: PHP dev server, queue worker, Laravel Pail (logs), and Vite. Kill with Ctrl+C.

## Testing

```bash
composer test
```
Clears config cache, then runs `php artisan test`. Tests use **in-memory SQLite** (configured in `phpunit.xml`).

Run a single test:
```bash
php artisan test --filter=ExampleTest
```

## Linting / Formatting

- **PHP**: Laravel Pint (`./vendor/bin/pint`). No Pint config — uses Laravel defaults.
- **JS/CSS**: No ESLint or Prettier. Tailwind v4 + Chart.js.

## Architecture

**Backend (Laravel API)**
- Routes: `routes/api.php` (RESTful endpoints)
- Models: User, Category, Transaction, Budget (PHP 8 attributes)
- Auth: Laravel Sanctum (token-based SPA)
- Controllers: Auth, Category, Transaction, Budget, Dashboard, Report

**Frontend (Vue 3 SPA)**
- Entry: `resources/js/app.js` → `resources/views/app.blade.php`
- Router: `resources/js/router/index.js` (Vue Router, history mode)
- Stores: Pinia (auth, categories, transactions, budgets)
- Composables: `useApi.js` (Axios with Sanctum token)
- Pages: Login, Register, Dashboard, Transactions, Categories, Budgets, Reports
- Components: Layout (bottom tab bar), modal forms
- Icons: Lucide Vue
- Charts: Chart.js (trend line, bar comparison)

## API Endpoints

```
POST   /api/register, /api/login, /api/logout
GET    /api/user
CRUD   /api/categories, /api/transactions, /api/budgets
GET    /api/dashboard, /api/reports/summary, /api/reports/by-category, /api/reports/trend
```

## Key Quirks

- `.npmrc` has `ignore-scripts=true` — npm postinstall scripts are skipped.
- Vite ignores `storage/framework/views/**` in watch mode.
- Tests disable Pulse, Telescope, and Nightwatch (`phpunit.xml` env vars).
- Default DB is SQLite (`database/database.sqlite`).
- Web routes serve `app.blade.php` for all paths (SPA catch-all).
- Mobile-first design: bottom tab bar, card-based lists, slide-up modals.
