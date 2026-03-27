# AFPPGMC Document Tracking - Local Network Deployment

This guide is for deploying on a local network (LAN) and serving users from a host machine.

## 1) Server Requirements

- PHP `8.2+`
- Composer `2+`
- Node.js `18+` and npm
- MySQL/MariaDB (recommended for production)
- Web server access to project folder
- PHP extensions: `pdo_mysql`, `mbstring`, `openssl`, `tokenizer`, `ctype`, `json`, `fileinfo`, `curl`, `xml`

## 2) First-Time Setup

From project root:

```powershell
composer install --no-dev --optimize-autoloader
npm install
copy .env.example .env
php artisan key:generate
```

Configure `.env`:

- `APP_ENV=production`
- `APP_DEBUG=false`
- `APP_URL=http://<SERVER_IP>:8000` (or your domain/internal hostname)
- Set DB credentials (`DB_CONNECTION=mysql`, `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`)
- `QUEUE_CONNECTION=sync` (default in this project for simpler LAN deployment)
- Configure mail settings if email notifications are required

Run database and assets:

```powershell
php artisan migrate --force
php artisan db:seed --force
php artisan storage:link
npm run build
php artisan optimize
```

## 3) Database Setup (First-Time)

- Create an empty database on the DB server.
- Create a DB user and grant permissions to the database.
- Update `.env` with the correct DB connection settings.
- If migrating from an existing installation, export the old database and import it before running migrations.

## 3) Run the App (LAN Accessible)

```powershell
php artisan serve --host=0.0.0.0 --port=8000
```

Users connect using:

- `http://<SERVER_IP>:8000`

## 4) Required Background Process

This app uses scheduled overdue notifications. Keep scheduler running:

```powershell
php artisan schedule:work
```

If you later change `QUEUE_CONNECTION` from `sync` to `database`, also run:

```powershell
php artisan queue:work --tries=3 --timeout=120
```

## 5) Keep Scheduler/Queue Running (Windows LAN)

For an office LAN on Windows, keep these running reliably:

- Scheduler: use Task Scheduler to run this every 1 minute:
  - `php artisan schedule:run`
- Queue (only if `QUEUE_CONNECTION=database`): run `php artisan queue:work` as a scheduled task or service (e.g., via NSSM).

## 6) File Permissions

Ensure these folders are writable by the user running PHP:

- `storage/`
- `bootstrap/cache`

## 7) Firewall / LAN Access

- Allow inbound TCP on port `8000` in Windows Firewall (or use another allowed port).
- Users should connect to the host machine via its LAN IP/hostname.

## 8) Client Workstations

- Client PCs only need a modern browser (Chrome/Edge/Firefox).

## 9) Update Deployment

After pulling new code:

```powershell
composer install --no-dev --optimize-autoloader
npm install
npm run build
php artisan migrate --force
php artisan optimize:clear
php artisan optimize
```

Restart running app/scheduler processes.

## 10) Pre-Go-Live Checklist

- [ ] `APP_DEBUG=false`
- [ ] `APP_URL` points to actual LAN address/hostname
- [ ] `php artisan test` passes
- [ ] `php artisan route:list` works
- [ ] `storage:link` exists
- [ ] Scheduler is running (`schedule:work`)
- [ ] Mail server settings verified (if using email notifications)
- [ ] Backup plan for MySQL database is in place
- [ ] Windows Firewall allows inbound access on the chosen port
