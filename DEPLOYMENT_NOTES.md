# Deployment Notes - Storage Link

## Important: Storage Link Setup

For photos and files to be accessible in production, you **MUST** create a symbolic link from `public/storage` to `storage/app/public`.

## Production Server Information

- **Domain**: https://jbtech.com.np/
- **Project Path**: `repositories/cms/`
- **Photos Location**: `repositories/cms/storage/app/public/projects/photos/`
- **Gallery URL**: https://jbtech.com.np/admin/projects/1/gallery

### Steps to Fix Photo/File Access Issues:

1. **SSH into your production server**

2. **Navigate to your Laravel project root directory**
   ```bash
   cd repositories/cms
   ```
   (Adjust path if your project root is different)

3. **Remove existing storage link if it exists (broken or incorrect)**
   ```bash
   rm -f public/storage
   ```

4. **Create the storage link**
   ```bash
   php artisan storage:link
   ```

5. **Verify the link was created**
   ```bash
   ls -la public/storage
   ```
   You should see it pointing to `../storage/app/public`

6. **Set proper permissions** (if needed)
   ```bash
   chmod -R 755 storage
   chmod -R 755 public/storage
   chown -R www-data:www-data storage
   chown -R www-data:www-data public/storage
   ```
   (Replace `www-data` with your web server user if different)

### What This Does:

- Creates a symbolic link: `public/storage` → `storage/app/public`
- Allows web-accessible files stored in `storage/app/public` to be served via `/storage/` URL
- Photos are stored in: `storage/app/public/projects/photos/`
- Files are stored in: `storage/app/public/projects/files/`
- They become accessible via: `https://jbtech.com.np/storage/projects/photos/filename.jpg`

### If the Link Already Exists:

If you get an error that the link already exists, you may need to remove it first:
```bash
rm public/storage
php artisan storage:link
```

### Alternative: Manual Symlink (if artisan command doesn't work)

```bash
cd repositories/cms
ln -s ../storage/app/public public/storage
```

### Verify It's Working:

After creating the link, test by accessing:
- `https://jbtech.com.np/storage/projects/photos/[any-photo-filename]`
- Check the gallery page: https://jbtech.com.np/admin/projects/1/gallery

If you see a 404, check:
1. The symbolic link exists: `ls -la public/storage`
2. Files exist in `storage/app/public/projects/photos/`
3. Web server has read permissions on the storage directory
4. Web server can follow symbolic links (check server config - Apache needs `Options +FollowSymLinks`)

### Quick One-Line Fix:

```bash
cd repositories/cms && rm -f public/storage && php artisan storage:link && chmod -R 755 storage public/storage
```

