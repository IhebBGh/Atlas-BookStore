# Project Setup Guide

This is a Symfony 6.4 project. Follow these steps to run it on your computer.

## Prerequisites

- **PHP 8.1 or higher** (included in WAMP)
- **Composer** (PHP dependency manager)
- **MySQL** (included in WAMP) OR **PostgreSQL** (via Docker)
- **WAMP Server** (already installed based on your path)

## Step 1: Install Dependencies

If you haven't already installed Composer dependencies, run:

```bash
composer install
```

## Step 2: Database Setup

### Option A: Using MySQL (WAMP) - Recommended

1. Start WAMP Server and ensure MySQL is running
2. Create a database named `projec_db` in phpMyAdmin (or via MySQL command line):
   ```sql
   CREATE DATABASE projec_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
   ```
3. The `.env` file is already configured for MySQL with:
   - Host: `127.0.0.1`
   - Port: `3306`
   - Database: `projec_db`
   - User: `root`
   - Password: (empty - default WAMP setup)

   **If your MySQL has a password**, edit `.env` and change:
   ```
   DATABASE_URL="mysql://root:YOUR_PASSWORD@127.0.0.1:3306/projec_db?serverVersion=8.0&charset=utf8mb4"
   ```

### Option B: Using PostgreSQL (Docker)

1. Make sure Docker Desktop is installed and running
2. Start the PostgreSQL container:
   ```bash
   docker compose up -d
   ```
3. Edit `.env` and uncomment the PostgreSQL DATABASE_URL line, comment out the MySQL one

## Step 3: Run Database Migrations

Run the following command to create the database tables:

```bash
php bin/console doctrine:migrations:migrate
```

When prompted, type `yes` to confirm.

## Step 4: Clear Cache

Clear the Symfony cache:

```bash
php bin/console cache:clear
```

## Step 5: Start the Development Server

You have two options:

### Option A: Using Symfony CLI (Recommended)

If you have Symfony CLI installed:
```bash
symfony server:start
```

Then visit: `http://localhost:8000`

### Option B: Using PHP Built-in Server

```bash
php -S localhost:8000 -t public
```

Then visit: `http://localhost:8000`

### Option C: Using WAMP Apache

1. Configure Apache virtual host to point to the `public` directory
2. Or access via: `http://localhost/projec/public/`

## Troubleshooting

### Database Connection Issues

- Verify MySQL is running in WAMP
- Check database credentials in `.env`
- Ensure the database `projec_db` exists

### Permission Issues

- Make sure `var/` directory is writable
- On Windows, you may need to set proper permissions

### Missing Extensions

Ensure these PHP extensions are enabled in WAMP:
- pdo_mysql (or pdo_pgsql if using PostgreSQL)
- mbstring
- xml
- ctype
- iconv
- intl

## Additional Commands

- **Clear cache**: `php bin/console cache:clear`
- **View routes**: `php bin/console debug:router`
- **Create database**: `php bin/console doctrine:database:create`
- **Drop database**: `php bin/console doctrine:database:drop --force`
- **Run migrations**: `php bin/console doctrine:migrations:migrate`

