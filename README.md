# Open9 Backend

Laravel 13 + Livewire 4 administrative backend for courses, projects, blog, enrollments, payments, certificates and contact workflows.

## Setup

```bash
composer install
npm install
cp .env.example .env
php artisan key:generate
php artisan migrate:fresh --seed
npm run build
```

## Development

```bash
composer run dev
```

## Admin Access

- URL: `/admin/dashboard`
- Email: `admin@open9.dev`
- Password: `password`
- Role: `super-admin`

## Useful Commands

```bash
php artisan migrate:fresh --seed
php artisan test --compact
vendor/bin/pint --dirty --format agent
phpstan analyse
```

PostgreSQL is recommended for production. SQLite remains supported for local tests and development.
