# Troubleshooting Photo Display Issues

## Current Issue: "Not Found" Placeholder Showing

If you're seeing the "Not Found" placeholder image, check the browser console. The updated code now logs:
- The URL that failed to load
- The path stored in the database
- Whether the file exists in storage

## Common Causes & Solutions

### 1. Storage Symlink Missing (Most Common)

**Symptom**: All images show "Not Found", console shows 404 errors

**Solution**: Create the storage symlink on production server:
```bash
cd repositories/cms
rm -f public/storage
php artisan storage:link
chmod -R 755 storage public/storage
```

**Verify**:
```bash
ls -la public/storage
# Should show: public/storage -> ../storage/app/public
```

### 2. Incorrect Path Format

**Symptom**: Console shows path but file doesn't exist

**Check the path format**:
- Database should store: `projects/photos/filename.jpg` (no leading slash)
- URL generated: `/storage/projects/photos/filename.jpg`
- Actual file location: `storage/app/public/projects/photos/filename.jpg`

**If path has issues**, check the database:
```sql
SELECT id, name, photos FROM projects WHERE id = 1;
```

The `photos` JSON should look like:
```json
[
  {
    "name": "Album Name",
    "photos": [
      {
        "path": "projects/photos/abc123.jpg",
        "original_name": "photo.jpg",
        ...
      }
    ]
  }
]
```

### 3. File Doesn't Exist

**Symptom**: Console shows "Exists: false"

**Check if files exist**:
```bash
cd repositories/cms
ls -la storage/app/public/projects/photos/
```

**If directory doesn't exist**:
```bash
mkdir -p storage/app/public/projects/photos
chmod -R 755 storage/app/public
```

### 4. Web Server Permissions

**Symptom**: Files exist but still 404

**Fix permissions**:
```bash
cd repositories/cms
chmod -R 755 storage
chmod -R 755 public/storage
chown -R www-data:www-data storage public/storage
# (Replace www-data with your web server user)
```

### 5. Web Server Configuration

**For Apache**: Ensure `.htaccess` allows symlinks:
```apache
Options +FollowSymLinks
```

**For Nginx**: Symlinks should work by default, but verify `root` points to `public` folder.

## Debugging Steps

1. **Check Browser Console**
   - Look for the error messages with URL, path, and exists status
   - Note the exact URL that's failing

2. **Check Server Logs**
   ```bash
   tail -f storage/logs/laravel.log
   ```

3. **Test Direct Access**
   - Try accessing: `https://jbtech.com.np/storage/projects/photos/[filename]`
   - If this works, the symlink is fine
   - If 404, check symlink and permissions

4. **Verify Database Paths**
   - Check what's stored in the `photos` JSON column
   - Ensure paths don't have leading slashes or incorrect format

5. **Check File System**
   ```bash
   cd repositories/cms
   # Check if symlink exists
   ls -la public/storage
   
   # Check if files exist
   ls -la storage/app/public/projects/photos/
   
   # Check permissions
   stat storage/app/public/projects/photos/
   ```

## Quick Diagnostic Script

Run this on your production server to check everything:

```bash
cd repositories/cms

echo "=== Checking Storage Symlink ==="
if [ -L "public/storage" ]; then
    echo "✓ Symlink exists"
    ls -la public/storage
else
    echo "✗ Symlink missing - run: php artisan storage:link"
fi

echo ""
echo "=== Checking Storage Directory ==="
if [ -d "storage/app/public/projects/photos" ]; then
    echo "✓ Photos directory exists"
    echo "Files found:"
    ls -la storage/app/public/projects/photos/ | head -5
else
    echo "✗ Photos directory missing"
fi

echo ""
echo "=== Checking Permissions ==="
stat -c "%a %U:%G %n" storage/app/public/projects/photos 2>/dev/null || echo "Cannot check permissions"

echo ""
echo "=== Testing URL Generation ==="
php artisan tinker --execute="echo asset('storage/projects/photos/test.jpg');"
```

## Expected Console Output

When images load correctly, you should see:
- No errors in console
- Images display properly
- Network tab shows 200 status for image requests

When images fail, console will show:
```
Failed to load image: https://jbtech.com.np/storage/projects/photos/filename.jpg 
Path: projects/photos/filename.jpg 
Exists: true/false
```

Use this information to diagnose the specific issue.

