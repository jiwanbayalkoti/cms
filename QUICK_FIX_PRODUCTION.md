# Quick Fix: Make Photos Visible in Production

## Run These Commands on Your Production Server

SSH into your server and run:

```bash
# 1. Navigate to your project directory
cd repositories/cms

# 2. Remove any existing broken symlink
rm -f public/storage

# 3. Create the storage symlink
php artisan storage:link

# 4. Set proper permissions
chmod -R 755 storage
chmod -R 755 public/storage

# 5. Verify it worked
ls -la public/storage
```

You should see output like:
```
lrwxrwxrwx 1 www-data www-data ... public/storage -> ../storage/app/public
```

## Test It

After running the commands above, test by visiting:
- **Gallery**: https://jbtech.com.np/admin/projects/1/gallery
- **Direct photo URL**: https://jbtech.com.np/storage/projects/photos/[any-photo-filename]

## If It Still Doesn't Work

### Check Web Server Configuration

**For Apache**, ensure your `.htaccess` or virtual host config allows symlinks:
```apache
Options +FollowSymLinks
```

**For Nginx**, symlinks should work by default, but verify the `root` directive points to the `public` folder.

### Verify Files Exist

```bash
cd repositories/cms
ls -la storage/app/public/projects/photos/
```

You should see your photo files listed.

### Check Permissions

```bash
# Make sure web server can read the files
chmod -R 755 storage/app/public
chown -R www-data:www-data storage/app/public
```

(Replace `www-data` with your web server user if different)

## One-Line Command (Copy & Paste)

```bash
cd repositories/cms && rm -f public/storage && php artisan storage:link && chmod -R 755 storage public/storage && ls -la public/storage
```

