# Open9 Backend

Laravel 13 + Livewire 4 administrative backend for courses, projects, blog, enrollments, payments, certificates and contact workflows.

La raíz del repositorio es el document root (equivalente a `public_html`). Laravel vive en `sys/`.

## Setup

```bash
cd sys
composer install
npm install
cp .env.example .env
php artisan key:generate
php artisan migrate:fresh --seed
npm run build
```

Composer, npm y `php artisan` se ejecutan **solo** dentro de `sys/`.

## Development

```bash
cd sys
composer run dev
```

## Admin Access

- URL: `/admin/dashboard`
- Email: `admin@open9.dev`
- Password: `password`
- Role: `super-admin`

## Useful Commands

```bash
cd sys
php artisan migrate:fresh --seed
php artisan test --compact
vendor/bin/pint --dirty --format agent
phpstan analyse
```

En BanaHosting la aplicación queda en `~/public_html/sys`. Cron:

```bash
cd /home/USUARIO/public_html/sys && php artisan schedule:run >> /dev/null 2>&1
```

PostgreSQL is recommended for production when available. Shared hosting (BanaHosting) uses MySQL/MariaDB. SQLite remains supported for local tests and development.
