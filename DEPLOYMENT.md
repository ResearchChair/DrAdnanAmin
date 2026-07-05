# Production Deployment Guide

## Server Requirements

- PHP 8.2+ with extensions: `pdo_mysql`, `mbstring`, `xml`, `curl`, `gd`, `fileinfo`
- MySQL 8.0+
- Composer 2.x
- Node.js 18+ (for building assets once)
- SSL certificate for your domain

## Deployment Steps

### 1. Upload code and install dependencies

```bash
composer install --no-dev --optimize-autoloader
npm ci && npm run build
```

### 2. Environment configuration

Copy `.env.example` to `.env` and configure:

```env
APP_NAME="Academic Portfolio"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://yourdomain.com

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=personal_portfolio
DB_USERNAME=your_user
DB_PASSWORD=your_password

ACADEMIC_ORCID_ID=0000-0000-0000-0000
ACADEMIC_OPENALEX_EMAIL=you@yourdomain.com
```

```bash
php artisan key:generate
php artisan migrate --force
php artisan db:seed --class=PortfolioSeeder --force
php artisan storage:link
```

### 3. Web server (Apache)

Point document root to `public/`:

```apache
<VirtualHost *:443>
    ServerName yourdomain.com
    DocumentRoot "D:/path/to/personal/public"
    <Directory "D:/path/to/personal/public">
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
```

For WAMP subdirectory access during development: `http://localhost/personal/public/`

### 4. File permissions

Ensure `storage/` and `bootstrap/cache/` are writable by the web server.

### 5. Optimization

```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan icons:cache
```

### 6. Scheduled sync (cron)

Add to crontab:

```cron
* * * * * cd /path/to/personal && php artisan schedule:run >> /dev/null 2>&1
```

This runs weekly ORCID + OpenAlex sync via `publications:sync --enrich`.

### 7. Backups

- Database: daily `mysqldump` of `personal_portfolio`
- Files: backup `storage/app/public/` (uploads, gallery, profile photos)

## Admin Access

- URL: `https://yourdomain.com/admin`
- Default seeded login: `admin` / `admin`
- **Change the password immediately after first login**

## Manual Citation Updates

Google Scholar stats must be updated manually in **Admin → Profile → Citation Stats** (no public API available).
