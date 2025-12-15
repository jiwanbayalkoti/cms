# Live Server Storage Setup Guide

## Current Server Structure
- Application location: `/home/jbtechco/public_html/repositories/cms/`
- Public folder: `/home/jbtechco/public_html/repositories/cms/public/`
- Storage folder: `/home/jbtechco/public_html/repositories/cms/storage/app/public/`

## Steps to Make Images Visible

### 1. Create Storage Symlink

SSH into your server and run:

```bash
cd /home/jbtechco/public_html/repositories/cms
php artisan storage:link
```

This will create a symlink:
- From: `public_html/repositories/cms/public/storage`
- To: `public_html/repositories/cms/storage/app/public`

### 2. Verify Symlink

Check if the symlink exists:

```bash
ls -la /home/jbtechco/public_html/repositories/cms/public/storage
```

You should see it pointing to `../storage/app/public`

### 3. Set Correct Permissions

```bash
# Set storage directory permissions
chmod -R 755 /home/jbtechco/public_html/repositories/cms/storage
chmod -R 755 /home/jbtechco/public_html/repositories/cms/bootstrap/cache

# Set ownership (replace 'username' with your cPanel username)
chown -R username:username /home/jbtechco/public_html/repositories/cms/storage
chown -R username:username /home/jbtechco/public_html/repositories/cms/bootstrap/cache
```

### 4. Verify .env Configuration

Make sure your `.env` file has:

```env
APP_URL=http://jbtech.com.np/repositories/cms
# OR if using subdomain:
APP_URL=http://jbtech.com.np
```

### 5. Clear Cache

```bash
cd /home/jbtechco/public_html/repositories/cms
php artisan config:clear
php artisan cache:clear
php artisan view:clear
```

### 6. Test Image Access

After setup, test if images are accessible:
- Logo: `http://jbtech.com.np/repositories/cms/storage/companies/logo.jpg`
- Favicon: `http://jbtech.com.np/repositories/cms/storage/favicons/favicon.png`

## Alternative: If Symlink Doesn't Work

If symlinks are not supported, you can copy files directly:

```bash
# Create a script to sync storage files
rsync -av /home/jbtechco/public_html/repositories/cms/storage/app/public/ /home/jbtechco/public_html/repositories/cms/public/storage/
```

Then set up a cron job to sync periodically, or update the code to write directly to `public/storage/`.

## Troubleshooting

### Images Still Not Showing

1. **Check file permissions:**
   ```bash
   ls -la /home/jbtechco/public_html/repositories/cms/storage/app/public/companies/
   ```

2. **Check if files exist:**
   ```bash
   ls -la /home/jbtechco/public_html/repositories/cms/storage/app/public/companies/
   ls -la /home/jbtechco/public_html/repositories/cms/storage/app/public/favicons/
   ```

3. **Check web server can access files:**
   - Ensure `.htaccess` allows access to storage folder
   - Check if mod_rewrite is enabled

4. **Verify APP_URL in .env:**
   - Should match your actual domain
   - Include subdirectory if app is in subdirectory

### Common Issues

- **403 Forbidden:** Check file permissions and ownership
- **404 Not Found:** Verify symlink exists and points correctly
- **Images broken:** Check browser console for actual URL being requested

