# Deployment Guide
## Income & Expenses Calculator

This guide covers the steps needed to deploy the application to production.

---

## Pre-Deployment Checklist

- [ ] All critical security fixes have been applied
- [ ] Database migrations are ready
- [ ] Environment variables are documented
- [ ] SSL certificate is configured
- [ ] Domain is configured
- [ ] Backup strategy is in place

---

## Environment Setup

### 1. Create `.env` File

Copy the `.env.example` file (if it exists) or create a new `.env` file with the following minimum configuration:

```bash
cp .env.example .env  # If .env.example exists
# OR create .env manually
```

### 2. Required Environment Variables

Set these critical variables in your `.env` file:

```env
APP_NAME="Income & Expenses Calculator"
APP_ENV=production
APP_DEBUG=false
APP_KEY=base64:YOUR_GENERATED_KEY_HERE
APP_URL=https://yourdomain.com

LOG_CHANNEL=daily
LOG_LEVEL=error

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=your_database_name
DB_USERNAME=your_database_user
DB_PASSWORD=your_secure_password

SESSION_DRIVER=database
SESSION_SECURE_COOKIE=true
SESSION_SAME_SITE=strict

MAIL_MAILER=smtp
MAIL_HOST=your_smtp_host
MAIL_PORT=587
MAIL_USERNAME=your_email
MAIL_PASSWORD=your_password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS="noreply@yourdomain.com"
MAIL_FROM_NAME="${APP_NAME}"
```

### 3. Generate Application Key

```bash
php artisan key:generate
```

**Important:** Generate a unique key for production. Never use the development key.

---

## Database Setup

### 1. Create Database

Create a MySQL database for the application:

```sql
CREATE DATABASE your_database_name CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'your_database_user'@'localhost' IDENTIFIED BY 'your_secure_password';
GRANT ALL PRIVILEGES ON your_database_name.* TO 'your_database_user'@'localhost';
FLUSH PRIVILEGES;
```

### 2. Run Migrations

```bash
php artisan migrate --force
```

### 3. Seed Initial Data (Optional)

```bash
php artisan db:seed --class=AdminUserSeeder
```

**Note:** Change the default admin password immediately after seeding.

---

## File Permissions

Set proper file permissions:

```bash
# Set directory permissions
sudo chmod -R 755 storage bootstrap/cache
sudo chown -R www-data:www-data storage bootstrap/cache

# For production, these should be restrictive
sudo chmod -R 755 public
sudo chmod -R 755 vendor
```

---

## Storage Link

Create symbolic link for storage:

```bash
php artisan storage:link
```

---

## Optimization

Run these commands to optimize the application:

```bash
# Cache configuration
php artisan config:cache

# Cache routes
php artisan route:cache

# Cache views
php artisan view:cache

# Optimize autoloader
composer dump-autoload --optimize --no-dev

# Build frontend assets
npm install
npm run build
```

---

## Security Checklist

### Server Configuration

- [ ] SSL/TLS certificate installed and configured
- [ ] HTTPS redirect enabled
- [ ] Firewall configured
- [ ] Database only accessible from localhost
- [ ] PHP version 8.1 or higher
- [ ] Disable dangerous PHP functions

### Application Configuration

- [ ] `APP_DEBUG=false` in `.env`
- [ ] `APP_ENV=production` in `.env`
- [ ] `LOG_LEVEL=error` in `.env`
- [ ] `SESSION_SECURE_COOKIE=true` in `.env`
- [ ] Unique `APP_KEY` generated
- [ ] Strong database password
- [ ] `.env` file not accessible via web
- [ ] `storage/` and `bootstrap/cache/` writable by web server

### PHP Configuration

Update `php.ini`:

```ini
expose_php = Off
display_errors = Off
log_errors = On
error_log = /path/to/error.log
```

---

## Web Server Configuration

### Apache (.htaccess)

Ensure `public/.htaccess` exists and is configured correctly.

### Nginx

Example Nginx configuration:

```nginx
server {
    listen 80;
    server_name yourdomain.com;
    return 301 https://$server_name$request_uri;
}

server {
    listen 443 ssl http2;
    server_name yourdomain.com;
    root /path/to/your/app/public;

    ssl_certificate /path/to/certificate.crt;
    ssl_certificate_key /path/to/private.key;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";

    index index.php;

    charset utf-8;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.1-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

---

## Post-Deployment

### 1. Verify Application

- [ ] Application loads correctly
- [ ] Login page accessible
- [ ] Can log in with admin credentials
- [ ] All features working
- [ ] File uploads working
- [ ] Database operations working

### 2. Monitor Logs

```bash
# Check application logs
tail -f storage/logs/laravel.log

# Check error logs
tail -f /var/log/nginx/error.log  # or Apache error log
```

### 3. Test Security

- [ ] HTTPS working correctly
- [ ] Security headers present
- [ ] Can't access `.env` file
- [ ] Can't access `storage/` directly
- [ ] CSRF protection working

### 4. Set Up Backups

Configure automated backups for:
- Database
- Uploaded files (`storage/app/public`)
- `.env` file

---

## Troubleshooting

### 500 Error

1. Check `storage/logs/laravel.log`
2. Verify file permissions
3. Check `.env` configuration
4. Verify database connection

### Permission Errors

```bash
sudo chown -R www-data:www-data storage bootstrap/cache
sudo chmod -R 775 storage bootstrap/cache
```

### Database Connection Error

- Verify database credentials in `.env`
- Check database server is running
- Verify database user has proper permissions

---

## Maintenance Mode

Put the application in maintenance mode during updates:

```bash
php artisan down
```

Bring it back online:

```bash
php artisan up
```

---

## Updates

When updating the application:

1. Put in maintenance mode
2. Pull latest code
3. Run `composer install --no-dev --optimize-autoloader`
4. Run migrations: `php artisan migrate --force`
5. Clear caches: `php artisan optimize:clear`
6. Rebuild caches: `php artisan optimize`
7. Bring back online

---

## Support

For issues, check:
- Application logs: `storage/logs/laravel.log`
- Web server logs
- PHP error logs
- Database logs

