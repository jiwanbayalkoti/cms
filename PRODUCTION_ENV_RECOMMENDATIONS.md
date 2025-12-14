# Production Environment (.env) Configuration Recommendations
## For: http://jbtech.com.np/

## Critical Changes for Production:

### 1. Application Environment
```
APP_ENV=production
APP_DEBUG=false
APP_URL=http://jbtech.com.np
```

### 2. Database Configuration
```
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
# OR use your database server IP/hostname
# DB_HOST=your-database-host

DB_PORT=3306
DB_DATABASE=your_production_database_name
DB_USERNAME=your_production_db_username
DB_PASSWORD=your_secure_production_db_password
```

### 3. Application Key
```
# Generate a new application key if not already set
# Run: php artisan key:generate --force
APP_KEY=base64:your-generated-key-here
```

### 4. Session & Cache Configuration
```
SESSION_DRIVER=file
# OR for better performance:
# SESSION_DRIVER=redis (if Redis is available)

CACHE_DRIVER=file
# OR for better performance:
# CACHE_DRIVER=redis (if Redis is available)

QUEUE_CONNECTION=sync
# OR for background jobs:
# QUEUE_CONNECTION=database (if using queues)
```

### 5. Mail Configuration (if using email)
```
MAIL_MAILER=smtp
MAIL_HOST=smtp.your-email-provider.com
MAIL_PORT=587
MAIL_USERNAME=your-email@jbtech.com.np
MAIL_PASSWORD=your-email-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@jbtech.com.np
MAIL_FROM_NAME="${APP_NAME}"
```

### 6. Logging
```
LOG_CHANNEL=stack
LOG_LEVEL=error
# In production, only log errors and above
```

### 7. Security Settings
```
# Disable debug mode
APP_DEBUG=false

# Use secure session settings
SESSION_SECURE_COOKIE=true
SESSION_HTTP_ONLY=true
SESSION_SAME_SITE=lax

# If using HTTPS (recommended)
ASSET_URL=http://jbtech.com.np
```

### 8. Additional Recommendations

#### If using HTTPS (SSL Certificate):
```
APP_URL=https://jbtech.com.np
ASSET_URL=https://jbtech.com.np
SESSION_SECURE_COOKIE=true
```

#### File Upload Settings (if applicable):
```
FILESYSTEM_DISK=local
# OR for cloud storage:
# FILESYSTEM_DISK=s3
# AWS_ACCESS_KEY_ID=your-key
# AWS_SECRET_ACCESS_KEY=your-secret
# AWS_DEFAULT_REGION=your-region
# AWS_BUCKET=your-bucket
```

## Steps to Apply:

1. **Backup your current .env file:**
   ```bash
   cp .env .env.backup
   ```

2. **Update the .env file with production values**

3. **Clear and cache configuration:**
   ```bash
   php artisan config:clear
   php artisan cache:clear
   php artisan config:cache
   php artisan route:cache
   php artisan view:cache
   ```

4. **Set proper file permissions:**
   ```bash
   chmod 644 .env
   chmod -R 755 storage bootstrap/cache
   ```

5. **Generate application key (if needed):**
   ```bash
   php artisan key:generate --force
   ```

6. **Run migrations (if needed):**
   ```bash
   php artisan migrate --force
   ```

## Security Checklist:

- [ ] APP_DEBUG is set to `false`
- [ ] Strong database passwords are used
- [ ] APP_KEY is set and secure
- [ ] File permissions are correct (644 for .env, 755 for directories)
- [ ] .env file is not publicly accessible (check .htaccess or server config)
- [ ] Database credentials are secure
- [ ] HTTPS is configured (if possible)
- [ ] Regular backups are configured

## Important Notes:

1. **Never commit .env to version control** - it should be in .gitignore
2. **Use environment-specific database** - Don't use development database
3. **Enable error logging** - Check `storage/logs/laravel.log` for errors
4. **Set up monitoring** - Monitor application performance and errors
5. **Regular backups** - Backup database and files regularly

