# Repository Guidelines

## Project Structure & Module Organization

SICAM is a modular Laravel 12 application; Patrimonio is the first MVP, not the whole system. Keep controllers in `app/Http/Controllers`, models in `app/Models`, business workflows in `app/Services`, and routes in `routes/`. Blade pages belong in `resources/views`; use `layouts/` and `includes/` for shared markup. Frontend sources live in `resources/css` and `resources/js`. Database migrations, factories, and seeders live in `database/`. Tests are split between `tests/Feature` and `tests/Unit`.

## Build, Test, and Development Commands

- `composer install && npm install` installs dependencies.
- `copy .env.example .env && php artisan key:generate` prepares local configuration.
- `php artisan migrate` applies reviewed database migrations.
- `composer dev` starts Laravel, the queue, logs, and Vite.
- `npm run dev` runs Vite; `npm run build` creates production assets.
- `php artisan test` runs tests; add `--filter TestName` to focus one.
- `vendor/bin/pint` formats PHP code.

## Architecture, Style & Naming

Follow PSR-12 and `.editorconfig`: UTF-8, LF endings, four-space indentation (two for YAML), and a final newline. Use `PascalCase` for classes, `camelCase` for methods and variables, and `snake_case` for database columns. Keep controllers thin; use services, Eloquent relationships, eager loading, pagination, and transactions. Do not query from Blade.

Use traditional Blade directives and `@include`; do not introduce custom `<x-*>` components, React, Vue, Livewire, Inertia, or SPA architecture. Interface copy is Spanish. Figma “Sistema de Inventario” is the visual source of truth; locate the specific frame before implementing a screen.

## Testing Guidelines

Use PHPUnit 11. Name files `*Test.php` and methods descriptively, such as `test_asset_reassignment_preserves_history`. Prefer Feature tests for routes, authorization, validation, and database workflows; use Unit tests for isolated services. Add regression tests for fixes. No coverage threshold is configured; cover success, validation, and permission paths.

## Commits & Pull Requests

No Git history is currently available. Use short, imperative commits with a scope when useful: `feat(patrimonio): add asset status filter`. Pull requests must explain the change, tests, migration/configuration impact, and linked issue. Include responsive screenshots for UI work and identify the relevant Figma frame.

## Security & Configuration

Never commit `.env`, credentials, or Accesos secrets. Verify official API documentation; do not invent endpoints. Validate inputs and uploads, retain CSRF protection, and enforce permissions in the backend.
