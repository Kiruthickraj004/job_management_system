# Job Management System

A Laravel 12 REST API for managing job listings, user applications, saved jobs, and administrator job operations. Authentication is handled with Laravel Sanctum personal access tokens.

## Contents

- [Features](#features)
- [Technology](#technology)
- [Requirements](#requirements)
- [Installation](#installation)
- [Running the application](#running-the-application)
- [API overview](#api-overview)
- [Authentication](#authentication)
- [API reference](#api-reference)
- [Data model](#data-model)
- [Project structure](#project-structure)
- [Testing and code quality](#testing-and-code-quality)
- [Troubleshooting](#troubleshooting)
- [Security notes](#security-notes)

## Features

- User registration and login with Sanctum token authentication.
- Public job listing.
- Admin-only job creation, editing, and deletion.
- Authenticated users can apply for jobs and withdraw applications.
- Vacancy counts are decremented on application and restored on withdrawal.
- Authenticated users can save and unsave jobs.
- Users can view their own applications.
- Admins can view all applications.
- Database migrations for users, jobs, applications, saved jobs, sessions, cache, and Sanctum tokens.

## Technology

- PHP `^8.2`
- Laravel `^12.0`
- Laravel Sanctum `^4.0`
- MySQL/MariaDB or SQLite
- Node.js and npm
- Vite `^7.0.7`
- Tailwind CSS `^4.0.0`
- PHPUnit `^11.5.50`

## Requirements

Install the following before starting:

1. PHP 8.2 or newer with PDO enabled.
2. Composer.
3. Node.js and npm.
4. Git, if cloning the project.
5. Either SQLite or MySQL/MariaDB. XAMPP provides Apache and MySQL on Windows.

Check the installed versions from PowerShell:

```powershell
php --version
composer --version
node --version
npm --version
```

## Installation

Open PowerShell in the project directory:

```powershell
cd C:\xampp\htdocs\job_management_system
```

Install PHP and JavaScript dependencies:

```powershell
composer install
npm install
```

Create the environment file. This repository does not currently include a committed `.env.example`, so create `.env` manually or copy it from your deployment template:

```powershell
Copy-Item .env.example .env
```

If `.env.example` is unavailable, create `.env` with the minimum local configuration below:

```dotenv
APP_NAME="Job Management System"
APP_ENV=local
APP_KEY=
APP_DEBUG=true
APP_URL=http://127.0.0.1:8000

LOG_CHANNEL=stack
LOG_LEVEL=debug

DB_CONNECTION=sqlite
DB_DATABASE=C:/xampp/htdocs/job_management_system/database/database.sqlite

BROADCAST_CONNECTION=log
CACHE_STORE=file
FILESYSTEM_DISK=local
QUEUE_CONNECTION=database
SESSION_DRIVER=database
SESSION_LIFETIME=120
```

For SQLite, create the database file and generate the application key:

```powershell
New-Item -ItemType File -Path database\database.sqlite -Force
php artisan key:generate
php artisan migrate
```

### XAMPP MySQL configuration

To use MySQL instead, create a database in phpMyAdmin or the MySQL client, then use these values in `.env`:

```dotenv
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=job_management_system
DB_USERNAME=root
DB_PASSWORD=
```

Start MySQL in the XAMPP Control Panel before running migrations:

```powershell
php artisan key:generate
php artisan migrate
```

The project has no application seeder yet, so a new database contains no users or jobs. Register the first user through the API, or insert test data manually.

## Running the application

### Recommended development command

After the initial installation, run:

```powershell
composer run dev
```

This starts the Laravel server, queue listener, log viewer, and Vite development server together. The API is normally available at `http://127.0.0.1:8000`.

### Run services separately

Use separate terminals when you need more control:

```powershell
php artisan serve
```

```powershell
npm run dev
```

```powershell
php artisan queue:listen --tries=1 --timeout=0
```

For a production frontend build:

```powershell
npm run build
```

The root web route (`GET /`) serves the default `welcome` Blade view. The business functionality is exposed through the `/api` routes documented below.

## API overview

The base URL for local development is:

```text
http://127.0.0.1:8000/api
```

JSON requests should send:

```http
Accept: application/json
Content-Type: application/json
```

Protected endpoints also require:

```http
Authorization: Bearer YOUR_TOKEN
```

### Roles

- `user`: can list jobs, apply, withdraw, save, unsave, and view their applications.
- `admin`: has all user capabilities plus job CRUD and access to all applicants.

The current registration endpoint accepts an optional `role` value of `user` or `admin`. Do not expose unrestricted admin registration in production; restrict administrator creation to a trusted workflow.

## Authentication

Register a user:

```powershell
curl.exe -X POST http://127.0.0.1:8000/api/register `
  -H "Accept: application/json" `
  -H "Content-Type: application/json" `
  -d '{"name":"Jane User","email":"jane@example.com","password":"password123"}'
```

The response contains a `token`. Use it as a Bearer token for protected requests.

Register an admin for local testing:

```powershell
curl.exe -X POST http://127.0.0.1:8000/api/register `
  -H "Accept: application/json" `
  -H "Content-Type: application/json" `
  -d '{"name":"Admin User","email":"admin@example.com","password":"password123","role":"admin"}'
```

Login:

```powershell
curl.exe -X POST http://127.0.0.1:8000/api/login `
  -H "Accept: application/json" `
  -H "Content-Type: application/json" `
  -d '{"email":"jane@example.com","password":"password123"}'
```

Logout deletes the token used by the request:

```powershell
curl.exe -X POST http://127.0.0.1:8000/api/logout `
  -H "Accept: application/json" `
  -H "Authorization: Bearer YOUR_TOKEN"
```

## API reference

All paths below are relative to `/api`.

| Method | Endpoint | Auth | Purpose |
| --- | --- | --- | --- |
| `POST` | `/register` | Public | Create a user and return a token. Required: `name`, `email`, `password` (minimum 8 characters). Optional: `role` (`user` or `admin`). |
| `POST` | `/login` | Public | Authenticate with `email` and `password`; returns a user and token. |
| `GET` | `/jobs` | Public | Return all jobs. |
| `POST` | `/logout` | User | Revoke the current token. |
| `POST` | `/jobs/{job}/apply` | User | Apply for a job if vacancies remain and the user has not already applied. |
| `POST` | `/jobs/{job}/withdraw` | User | Withdraw the user's application and restore one vacancy. |
| `GET` | `/my-applications` | User | Return the authenticated user's applications. |
| `GET` | `/saved-jobs` | User | Return the authenticated user's saved jobs, including job data. |
| `POST` | `/jobs/{job}/save` | User | Save a job. |
| `DELETE` | `/jobs/{job}/unsave` | User | Remove a saved job. |
| `POST` | `/jobs` | Admin | Create a job. Required: `title`, `description`, `vacancy`, `total_vacancy`. |
| `PUT` | `/jobs/{job}` | Admin | Update `title`, `description`, `vacancy`, `total_vacancy`, and `status` (`open` or `closed`). |
| `DELETE` | `/jobs/{job}` | Admin | Delete a job. |
| `GET` | `/applicants` | Authenticated | Return all applications. Intended for admin use; currently the controller does not enforce the admin check. |

### Create a job

```powershell
curl.exe -X POST http://127.0.0.1:8000/api/jobs `
  -H "Accept: application/json" `
  -H "Content-Type: application/json" `
  -H "Authorization: Bearer ADMIN_TOKEN" `
  -d '{"title":"Backend Developer","description":"Build and maintain APIs.","vacancy":3,"total_vacancy":3}'
```

### Apply for a job

```powershell
curl.exe -X POST http://127.0.0.1:8000/api/jobs/1/apply `
  -H "Accept: application/json" `
  -H "Authorization: Bearer USER_TOKEN"
```

### Save and list saved jobs

```powershell
curl.exe -X POST http://127.0.0.1:8000/api/jobs/1/save `
  -H "Accept: application/json" `
  -H "Authorization: Bearer USER_TOKEN"

curl.exe http://127.0.0.1:8000/api/saved-jobs `
  -H "Accept: application/json" `
  -H "Authorization: Bearer USER_TOKEN"
```

Validation failures normally return HTTP `422`. Unauthenticated requests return HTTP `401`, and admin-only job operations return HTTP `403` for non-admin users.

## Data model

- `users`: name, unique email, hashed password, and role.
- `jobs`: owner (`user_id`), title, description, available vacancy, total vacancy, and status.
- `applications`: user/job relationship, status (`applied` or `withdrawn`), and timestamps. Each user can have one application per job.
- `saved_jobs`: user/job relationship. Each user can save a job once.
- `personal_access_tokens`: Sanctum tokens used by the API.

Foreign keys use cascade deletion. Deleting a user or job also removes related applications and saved jobs.

## Project structure

```text
app/Http/Controllers/Api/  API controllers
app/Models/                 Eloquent models
bootstrap/                  Laravel application bootstrap
config/                     Application configuration
database/migrations/        Database schema
database/seeders/           Seeder entry point
resources/css/              Tailwind styles
resources/js/               Vite JavaScript entry point
resources/views/            Blade views
routes/api.php              API routes
routes/web.php              Web routes
tests/                      PHPUnit tests
```

## Testing and code quality

Run the test suite:

```powershell
composer test
```

The test script clears configuration before running `php artisan test`.

Format PHP files with Laravel Pint:

```powershell
vendor\bin\pint
```

Inspect available Artisan commands:

```powershell
php artisan list
```

Reset and rebuild the local database when needed. This deletes all local data:

```powershell
php artisan migrate:fresh
```

## Troubleshooting

### `No application encryption key has been specified`

Ensure `.env` exists, then run:

```powershell
php artisan key:generate
```

### Database connection errors

Confirm that `DB_CONNECTION` and the other database values in `.env` match the selected database. For MySQL, ensure MySQL is running in XAMPP and the database already exists.

### `could not find driver`

Enable the matching PHP extension in the active `php.ini`: `pdo_sqlite` for SQLite or `pdo_mysql` for MySQL. Restart the terminal after changing PHP configuration.

### Port 8000 is already in use

Start Laravel on another port:

```powershell
php artisan serve --port=8001
```

Update the API base URL in your client accordingly.

### Vite cannot start

Reinstall JavaScript dependencies and check Node.js:

```powershell
Remove-Item -Recurse -Force node_modules
npm install
npm run dev
```

### `composer run setup` cannot find `.env.example`

The current Composer setup script references `.env.example`, but that file is not present in this repository. Follow the manual environment setup above, then run `php artisan key:generate`, `php artisan migrate`, and `npm run build`.

## Security notes

- Never commit `.env`, application keys, or access tokens.
- Use HTTPS outside local development.
- Restrict admin account creation before deploying this API.
- Review and tighten the `/applicants` authorization check before production; the current route requires authentication but the controller does not currently verify the user's role.
- Validate vacancy values and ownership rules further if this API is exposed to untrusted clients.

## License

This project uses Laravel and is configured with the MIT license in `composer.json`. Add the project's own license terms here if they differ.
