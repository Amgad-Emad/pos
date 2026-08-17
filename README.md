# نظام المبيعات — POS System

An Arabic (RTL) point-of-sale system built with Laravel. It covers the full daily flow of a retail shop:

- **Sales (المبيعات)** — a fast POS screen with live product search, cart, discounts, and partial payments.
- **Returns (المرتجعات)** — return items against an invoice; the refund uses each item's *discount-weighted* price, so a 200 EGP item on an invoice with a 25% overall discount refunds 150 EGP, not 200.
- **Invoices (فواتير المبيعات)** — printable A4 and 80mm receipt formats, with the shop's name and logo.
- **Inventory, Products, Categories (المخزن والمنتجات والتصنيفات)** — stock is decremented on sale and restored on return automatically.
- **Suppliers & Purchases (الموردين والمشتريات)** — purchase invoices that add stock.
- **Users & Roles (المستخدمين)** — `admin` sees everything; `seller` is limited to selling, returns, inventory, and their own invoices.
- **Dashboard & Settings** — sales charts, low-stock alerts, and shop identity (name / logo / phone / address) shown on printed invoices.

## Requirements

Installing on a completely new device, you need:

| Tool | Version | Notes |
|---|---|---|
| PHP | 8.2 or newer | works with the PHP 8.2 bundled with XAMPP; needs the default extensions Laravel uses (`pdo_sqlite`, `mbstring`, `openssl`, `fileinfo`, `gd` or `imagick` for the shop logo) |
| Composer | 2.x | dependency manager for PHP |
| SQLite | any | bundled with PHP — no database server to install |

Node.js is **not required** to run the app — all CSS/JS assets are pre-built and committed under `public/`.

The easiest way to get PHP + Composer on a new machine:

- **macOS / Windows:** install [Laravel Herd](https://herd.laravel.com) — one installer that ships PHP, Composer, and a local web server.
- **Windows with XAMPP:** see the dedicated [Windows + XAMPP](#installation-on-windows--xampp) section below.
- **Linux (Ubuntu/Debian):**
  ```bash
  sudo apt update
  sudo apt install php php-cli php-sqlite3 php-mbstring php-xml php-curl php-gd php-zip unzip
  # Composer:
  curl -sS https://getcomposer.org/installer | php
  sudo mv composer.phar /usr/local/bin/composer
  ```

## Installation (macOS / Linux / Herd)

Windows + XAMPP users: follow the [dedicated section below](#installation-on-windows--xampp) instead.

```bash
# 1. Get the code
git clone <repository-url> pos
cd pos

# 2. Install PHP dependencies
composer install

# 3. Create your environment file and app key
cp .env.example .env
php artisan key:generate

# 4. Create the SQLite database file and run migrations
touch database/database.sqlite
php artisan migrate

# 5. Seed roles, permissions, and users
php artisan db:seed
```

> **Important:** step 5 prints the **admin password** to the terminal — it is generated randomly and shown **only once**. Copy it before closing the terminal.

```bash
# 6. Link public storage (needed for the shop logo upload)
php artisan storage:link

# 7. Serve the app
php artisan serve
```

Open <http://localhost:8000> and log in.

If you use **Herd** or **Valet**, skip step 7 — place the project in your parked folder and open `https://pos.test` (then set `APP_URL=https://pos.test` in `.env`).

## Installation on Windows + XAMPP

Use this path if you prefer XAMPP over Herd on a fresh Windows machine.

### 1. Install the tools

1. Download and install **[XAMPP](https://www.apachefriends.org/download.html)** — the standard installer with its bundled **PHP 8.2** is fully supported (any newer PHP works too). Installing to the default `C:\xampp` is assumed below.
2. Download and install **[Git for Windows](https://git-scm.com/download/win)**.
3. Download and install **[Composer-Setup.exe](https://getcomposer.org/Composer-Setup.exe)**. During setup, when asked for the PHP to use, point it to `C:\xampp\php\php.exe`.

### 2. Enable the required PHP extensions

Open `C:\xampp\php\php.ini` in a text editor and make sure these lines exist **without** a leading semicolon (remove the `;` if present):

```ini
extension=pdo_sqlite
extension=sqlite3
extension=gd
extension=fileinfo
extension=mbstring
extension=curl
extension=openssl
extension=zip
```

Most are already enabled in XAMPP; `gd`, `sqlite3`, and `zip` are the usual ones that need uncommenting. If Apache is running, restart it from the XAMPP Control Panel after saving.

### 3. Install the app

Open **Command Prompt** (`cmd`) and run:

```bat
:: 1. Get the code (htdocs is convenient, but any folder works)
cd C:\xampp\htdocs
git clone <repository-url> pos
cd pos

:: 2. Install PHP dependencies
composer install

:: 3. Create your environment file and app key
copy .env.example .env
php artisan key:generate

:: 4. Create the SQLite database file and run migrations
type nul > database\database.sqlite
php artisan migrate

:: 5. Seed roles, permissions, and users
php artisan db:seed
```

> **Important:** step 5 prints the **admin password** — it is generated randomly and shown **only once**. Copy it before closing the window.

```bat
:: 6. Link public storage (needed for the shop logo upload)
php artisan storage:link

:: 7. Serve the app
php artisan serve
```

Open <http://localhost:8000> and log in. No MySQL setup is needed — the app uses SQLite, so you don't have to start the MySQL service or create a database in phpMyAdmin.

> `php artisan storage:link` creates a symbolic link; on Windows this may require running the terminal **as Administrator** (or enabling Windows *Developer Mode* in Settings → For developers).

### Optional: serve through Apache at http://pos.localhost

`php artisan serve` is the simplest way to run the app, but if you want it served by XAMPP's Apache instead:

1. Open `C:\xampp\apache\conf\extra\httpd-vhosts.conf` and add:

   ```apache
   <VirtualHost *:80>
       ServerName pos.localhost
       DocumentRoot "C:/xampp/htdocs/pos/public"
       <Directory "C:/xampp/htdocs/pos/public">
           AllowOverride All
           Require all granted
       </Directory>
   </VirtualHost>
   ```

2. Restart Apache from the XAMPP Control Panel.
3. Set `APP_URL=http://pos.localhost` in `.env`.
4. Open <http://pos.localhost>.

The `DocumentRoot` **must** point to the `public` folder — never expose the project root through Apache.

## Default accounts

| Role | Email | Password |
|---|---|---|
| مدير (admin) | `admin@pos.test` | printed by `php artisan db:seed` |
| بائع (seller) | `seller1@pos.test` | `password` |
| بائع (seller) | `seller2@pos.test` | `password` |

Outside production, the seeder also loads sample categories, suppliers, products, and 30 days of sales so the dashboard isn't empty. To start with a clean database instead, seed only the essentials:

```bash
php artisan migrate:fresh
php artisan db:seed --class=RolesAndPermissionsSeeder
php artisan db:seed --class=UserSeeder
```

## After installation

1. Log in as admin and open **الإعدادات (Settings)** to set the shop name, logo, phone, and address — these appear on all printed invoices.
2. Add your categories, suppliers, and products (or record purchase invoices, which add stock automatically).
3. Sellers can then use **المبيعات → فاتورة بيع جديدة** to sell, and **المرتجعات → إذن إرجاع جديد** to process returns by invoice number.

## Running tests

```bash
php artisan test
```

## Updating an existing installation

```bash
git pull
composer install
php artisan migrate
php artisan db:seed --class=RolesAndPermissionsSeeder   # refresh permissions if new ones were added
php artisan config:clear
```

## Tech stack

- [Laravel 13](https://laravel.com) + PHP 8.3, SQLite
- [spatie/laravel-permission](https://spatie.be/docs/laravel-permission) — roles & permissions
- [spatie/laravel-medialibrary](https://spatie.be/docs/laravel-medialibrary) — shop logo storage
- Bootstrap 5 (RTL) + [Alpine.js](https://alpinejs.dev) + [Lucide](https://lucide.dev) icons (pre-built, served from `public/dashboard/assets`)
- [Pest](https://pestphp.com) for tests
