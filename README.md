# Atlas Bookshop

Atlas Bookshop is a Symfony e-commerce application that combines a public storefront with a full-featured admin area. It provides product management, user authentication and roles, order processing, messaging, and an advanced admin dashboard with dynamic metrics and notifications.

---

## Key Features

- Product management: CRUD for products with image upload support.
- User authentication and role management (`ROLE_USER`, `ROLE_ADMIN`).
- Orders management with listing and detail views.
- Contact/messaging system with unread tracking.
- Admin dashboard with dynamic metrics (products, users, orders, messages), recent activity, notifications, and quick actions.
- Console utilities:
	- `app:reset-user-password` — hash & set a user's password.
	- `app:create-admin` — grant `ROLE_ADMIN` to a user.

## Tech Stack

- PHP 8+ and Symfony Framework
- Doctrine ORM
- Twig templates
- Bootstrap 5 + Bootstrap Icons
- (Optional) Vite / Webpack Encore for asset bundling

## Repository Layout (important files)

- `src/Controller` — controllers (including `AdminController.php`)
- `src/Entity` — Doctrine entities (`User`, `Product`, `Order`, `Message`, ...)
- `src/Repository` — repositories (ProductRepository, OrderRepository, MessageRepository, ...)
- `templates/` — Twig templates (admin UI under `templates/Admin/`)
- `config/packages/security.yaml` — security configuration

## Local Setup (development)

1. Clone the repository

```powershell
git clone <your-repo-url>.git
cd <repo-folder>
```

2. Install dependencies

```powershell
composer install
```

3. Configure environment

- Copy `.env` to `.env.local` and update the necessary values:

```
DATABASE_URL="mysql://db_user:db_password@127.0.0.1:3306/db_name"
MAILER_DSN=smtp://...
APP_ENV=dev
APP_SECRET=your_secret_here
```

4. Create database and run migrations

```powershell
php bin/console doctrine:database:create
php bin/console doctrine:migrations:migrate
```

5. Add a user

- Register via the web `/register` (passwords are hashed automatically)

- Or create a DB user and set a hashed password via console:

```powershell
php bin/console app:reset-user-password "user@example.com" "PlainPassword123"
```

6. Grant admin role (if required)

```powershell
php bin/console app:create-admin "user@example.com"
```

7. Run the dev server

```powershell
# Symfony CLI
symfony server:start

# or PHP built-in server
php -S 127.0.0.1:8000 -t public
```

## Admin Dashboard Notes

- Admin routes require `ROLE_ADMIN`.
- Controller: `src/Controller/AdminController.php` provides dynamic variables consumed by the dashboard:
	- `totalProducts`, `totalUsers`, `adminUsers`, `regularUsers`, `totalOrders`, `totalMessages`, `unreadMessages`, `notificationsCount`, `recentOrders`, `recentMessages`.
- Templates:
	- `templates/Admin/layout.html.twig` — admin shell (topbar, sidebar, content)
	- `templates/Admin/dashboard.html.twig` — dashboard content
- If dropdowns are clipped or appear under other elements, check `templates/base.html.twig` for `z-index` adjustments.

## Useful Commands

- Clear cache:

```powershell
php bin/console cache:clear
```

- Generate a password hash:

```powershell
php bin/console security:hash-password 'plaintext'
```

- Quick DB query (example):

```powershell
php bin/console doctrine:query:sql "SELECT id,email,roles FROM `user` WHERE email='user@example.com'"
```

## Troubleshooting

- Login fails: ensure the `password` column contains a hashed value (not plaintext) and `roles` is valid JSON (e.g. `["ROLE_ADMIN"]`).
- Check logs: `var/log/dev.log` or `var/log/prod.log`.
- If admin dropdowns are hidden behind page content, confirm `.dropdown-menu` and navbar `z-index` in `templates/base.html.twig`.

## Contributing

- Fork → feature branch → pull request.
- Include migrations and tests for substantial changes.
- Prefer moving large CSS blocks to a dedicated asset file (SCSS/CSS) and use your asset pipeline for production.

## License

Add a `LICENSE` file with your chosen license.

---

If you want, I can:
- create demo fixtures to seed products, users, orders and messages for local testing,
- extract admin inline CSS into a dedicated stylesheet and wire it into the asset build,
- or add the README in another format (e.g., `README.txt`).

Tell me which of these I should do next and I will apply it.
