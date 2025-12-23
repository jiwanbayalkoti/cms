# Public HTML Storage Setup

## Overview

Images and files are now stored directly in the `public_html` directory instead of `storage/app/public`. This makes files directly accessible via the web without requiring symlinks.

## Configuration

### Filesystem Disk

A new `public_html` disk has been added to `config/filesystems.php`:

```php
'public_html' => [
    'driver' => 'local',
    'root' => env('PUBLIC_HTML_PATH', base_path('../public_html')),
    'url' => env('APP_URL', ''),
    'visibility' => 'public',
    'throw' => false,
],
```

### Environment Variable (Optional)

If your `public_html` directory is in a different location, add to your `.env` file:

```env
PUBLIC_HTML_PATH=/path/to/public_html
```

## File Storage Structure

- **Photos**: `public_html/projects/photos/`
- **Files**: `public_html/projects/files/`

## URL Generation

Files are accessible directly via:
- Photos: `https://jbtech.com.np/projects/photos/filename.jpg`
- Files: `https://jbtech.com.np/projects/files/filename.pdf`

## Changes Made

1. **New filesystem disk** (`public_html`) in `config/filesystems.php`
2. **Updated ProjectController** to store photos/files in `public_html`
3. **Updated StorageHelper** to generate URLs pointing to `public_html`
4. **Updated file deletion** code to use `public_html` disk

## Server Setup

### Create Directories

On your production server, ensure the directories exist:

```bash
# Navigate to public_html (adjust path as needed)
cd /path/to/public_html

# Create directories
mkdir -p projects/photos
mkdir -p projects/files

# Set permissions
chmod -R 755 projects
chown -R www-data:www-data projects
```

### Verify Path

If your Laravel app is in `repositories/cms/` and `public_html` is at the same level:

```bash
# From Laravel root (repositories/cms/)
ls -la ../public_html/projects/photos/
```

If `public_html` is in a different location, set the `PUBLIC_HTML_PATH` environment variable.

## Migration Notes

### Existing Files

If you have existing files in `storage/app/public/projects/photos/`, you may want to:

1. **Move existing files**:
   ```bash
   mv storage/app/public/projects/photos/* public_html/projects/photos/
   ```

2. **Update database paths** (if needed):
   - Old path format: `projects/photos/filename.jpg`
   - New path format: `projects/photos/filename.jpg` (same, but stored in different location)

### Testing

After deployment:

1. Upload a new photo through the project gallery
2. Verify it's stored in `public_html/projects/photos/`
3. Check that it's accessible via: `https://jbtech.com.np/projects/photos/[filename]`
4. Verify the gallery displays the image correctly

## Benefits

- ✅ No symlink required
- ✅ Direct web access to files
- ✅ Simpler deployment
- ✅ Files accessible via standard URLs

## Notes

- Make sure `public_html` has proper write permissions for the web server
- Consider adding `.htaccess` rules to protect sensitive files if needed
- Backup files before migration if moving existing files

