# Product Admin

A product management system built with Laravel 13 and MySQL. It provides an
admin dashboard for managing products, a public catalogue for visitors, and a
token-authenticated REST API.

- **Catalogue** (public) — `/`
- **Admin dashboard** (admin login required) — `/admin/products`
- **API documentation** — `/docs/api`

## Project setup

### Requirements

| Software | Version |
| --- | --- |
| PHP | 8.3 or newer |
| Composer | 2.x |
| Node.js + npm | Node 20 or newer |
| MySQL | 8.x |

### Steps

**1. Install PHP dependencies**

```bash
composer install
```

**2. Create the environment file and generate an application key**

```bash
cp .env.example .env
php artisan key:generate
```

`.env` is not committed to the repository, so this step is required on every
fresh clone. Without an `APP_KEY` the application cannot start.

**3. Configure MySQL in `.env`**

```dotenv
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=my_laravel_app
DB_USERNAME=root
DB_PASSWORD=
```

Use `127.0.0.1` rather than `localhost`. With `localhost`, the MySQL client
tries a Unix socket at its compiled-in default path instead of connecting over
TCP, which fails for MAMP and similar bundles that place their socket elsewhere.

> **Using MAMP?** Check **Preferences → Ports** for the MySQL port. MAMP's own
> default is `8889`, but selecting "Set Web & MySQL ports to 80 & 3306" changes
> it to `3306`. Set `DB_PORT` to whichever is actually in use.

**4. Create the database schema and demo data**

```bash
php artisan migrate --seed
```

If the database named in `DB_DATABASE` does not exist yet, `migrate` will warn
and offer to create it. Answer yes, or create it yourself first:

```sql
CREATE DATABASE my_laravel_app CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

The `--seed` flag is **required for the login accounts below to exist** — they
are created by the seeder, not by the migrations. Seeding also inserts a demo
catalogue of 20 products across 4 categories (16 enabled, 4 disabled).

**5. Build the front-end assets**

```bash
npm install
npm run build
```

This step is **required**, not optional. The Blade layout loads its CSS and
JavaScript through Vite, and the compiled manifest is not committed to the
repository. Skipping it makes every page fail with HTTP 500 and the error
"Unable to locate file in Vite manifest".

While actively changing Blade templates or CSS, run `npm run dev` instead and
leave it running.

**6. Start the application**

```bash
php artisan serve
```

The site is then available at http://127.0.0.1:8000.

### Login accounts

Created by the seeder in step 4.

| Role | Email | Password |
| --- | --- | --- |
| Administrator | `admin@example.com` | `password` |
| Customer | `customer@example.com` | `password` |

Only the administrator can reach `/admin/products` or the product API; the
customer account exists to demonstrate that non-admins are rejected with a
`403`. These are development credentials — change them before deploying
anywhere public.

### Resetting the data

```bash
php artisan migrate:fresh --seed
```

This drops every table and re-seeds. It also clears the `sessions` table, so
any open browser session is logged out.

## API endpoints documentation

Interactive documentation is served at:

```
{domain}/docs/api
```

For a local server that is http://127.0.0.1:8000/docs/api.

The page is a [Swagger UI](https://swagger.io/tools/swagger-ui/) browser for the
OpenAPI 3.0 specification in `public/openapi.yaml`. The documentation page
itself is public and needs no login. Swagger UI is loaded from a CDN, so the
page needs an internet connection; the specification itself is served locally
and can be read or downloaded directly at `{domain}/openapi.yaml`.

### Assumptions
- Assume the business sells a fixed set of product categories, so there is no category module.

### Design Choice
- The product listing is sorted by the latest created date, which gives a better user experience.
- Price uses the decimal:2 data type instead of float.

---

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

Laravel has the most extensive and thorough [documentation](https://laravel.com/docs) and video tutorial library of all modern web application frameworks, making it a breeze to get started with the framework.

In addition, [Laracasts](https://laracasts.com) contains thousands of video tutorials on a range of topics including Laravel, modern PHP, unit testing, and JavaScript. Boost your skills by digging into our comprehensive video library.

You can also watch bite-sized lessons with real-world projects on [Laravel Learn](https://laravel.com/learn), where you will be guided through building a Laravel application from scratch while learning PHP fundamentals.

## Agentic Development

Laravel's predictable structure and conventions make it ideal for AI coding agents like Claude Code, Cursor, and GitHub Copilot. Install [Laravel Boost](https://laravel.com/docs/ai) to supercharge your AI workflow:

```bash
composer require laravel/boost --dev

php artisan boost:install
```

Boost provides your agent 15+ tools and skills that help agents build Laravel applications while following best practices.

## Contributing

Thank you for considering contributing to the Laravel framework! The contribution guide can be found in the [Laravel documentation](https://laravel.com/docs/contributions).

## Code of Conduct

In order to ensure that the Laravel community is welcoming to all, please review and abide by the [Code of Conduct](https://laravel.com/docs/contributions#code-of-conduct).

## Security Vulnerabilities

If you discover a security vulnerability within Laravel, please send an e-mail to Taylor Otwell via [taylor@laravel.com](mailto:taylor@laravel.com). All security vulnerabilities will be promptly addressed.

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
