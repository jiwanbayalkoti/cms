# cPanel Storage Link Fix for Laravel

## Project Setup
- **Project Path**: `public_html/repositories/cms`
- **Storage Location**: `storage/app/public/projects/photos`
- **Public Directory**: `public_html/repositories/cms/public`
- **Symlink Target**: `public_html/repositories/cms/public/storage` → `public_html/repositories/cms/storage/app/public`

## Fix Steps

### 1. Create Storage Symlink

SSH into your cPanel server and run:

```bash
cd public_html/repositories/cms

# Remove existing symlink if it exists
rm -f public/storage

# Create the symlink
php artisan storage:link

# Verify the symlink
ls -la public/storage
# Should show: public/storage -> ../storage/app/public
```

### 2. Set Permissions

```bash
cd public_html/repositories/cms

# Set permissions for storage directory
chmod -R 755 storage
chmod -R 755 public/storage

# Set ownership (replace username with your cPanel username)
chown -R username:username storage
chown -R username:username public/storage
```

### 3. Verify Directory Structure

```bash
cd public_html/repositories/cms

# Check storage directory exists
ls -la storage/app/public/projects/photos/

# Check symlink exists
ls -la public/storage

# Should show: lrwxrwxrwx ... public/storage -> ../storage/app/public
```

## URL Generation

The code now uses `asset('storage/' . $path)` which will generate:
- **Path in DB**: `projects/photos/filename.jpg`
- **Generated URL**: `/repositories/cms/storage/projects/photos/filename.jpg`
- **Full URL**: `https://yourdomain.com/repositories/cms/storage/projects/photos/filename.jpg`

## Testing

1. **Upload a test photo** through the project gallery
2. **Check it's stored**: `ls -la storage/app/public/projects/photos/`
3. **Test direct access**: Visit `https://yourdomain.com/repositories/cms/storage/projects/photos/[filename]`
4. **Check gallery**: Visit the gallery page and verify images load

## Troubleshooting

### If images still don't load:

1. **Check symlink exists**:
   ```bash
   ls -la public_html/repositories/cms/public/storage
   ```

2. **Check file permissions**:
   ```bash
   ls -la storage/app/public/projects/photos/
   ```

3. **Check web server can follow symlinks**:
   - For Apache, ensure `.htaccess` has: `Options +FollowSymLinks`
   - For Nginx, symlinks should work by default

4. **Check .htaccess in public directory**:
   ```apache
   <IfModule mod_rewrite.c>
       Options +FollowSymLinks
       RewriteEngine On
       RewriteRule ^storage/(.*)$ storage/$1 [L]
   </IfModule>
   ```

5. **Verify APP_URL in .env**:
   ```env
   APP_URL=https://yourdomain.com
   ```

## Alternative: Manual Symlink

If `php artisan storage:link` doesn't work:

```bash
cd public_html/repositories/cms
ln -s ../storage/app/public public/storage
```

## Files Updated

- ✅ `app/Http/Controllers/Admin/ProjectController.php` - Uses 'public' disk
- ✅ `app/Helpers/StorageHelper.php` - Uses `asset('storage/' . $path)` for URL generation
- ✅ Views updated to use StorageHelper

## Notes

- The symlink must be created in the `public` directory
- Files are stored in `storage/app/public/projects/photos/`
- URLs are generated via the symlink at `public/storage`
- This works with standard Laravel storage structure

