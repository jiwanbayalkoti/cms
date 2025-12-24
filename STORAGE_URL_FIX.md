# Storage URL Fix - cPanel Shared Hosting

## Problem
- Wrong URL: `https://jbtech.com.np/projects/photos/filename.jpg` (404)
- Correct URL: `https://jbtech.com.np/repositories/cms/storage/projects/photos/filename.jpg`

## Folder Structure
```
public_html/
└── repositories/
    └── cms/                    (Laravel root)
        ├── public/
        │   └── storage/        (symlink → ../storage/app/public)
        ├── storage/
        │   └── app/
        │       └── public/
        │           └── projects/
        │               └── photos/
        │                   └── filename.jpg
        └── config/
            └── filesystems.php
```

## Fixes Applied

### 1. StorageHelper.php
```php
public static function url(?string $path): ?string
{
    if (!$path) return null;
    $path = ltrim($path, '/');
    $basePath = '/repositories/cms';
    return url($basePath . '/storage/' . $path);
}
```

**Result**: Generates `/repositories/cms/storage/projects/photos/filename.jpg`

### 2. config/filesystems.php
```php
'public' => [
    'driver' => 'local',
    'root' => storage_path('app/public'),
    'url' => env('APP_URL', 'http://localhost') . '/repositories/cms/storage',
    'visibility' => 'public',
    'throw' => false,
],
```

### 3. All Views Use StorageHelper
- `resources/views/admin/projects/gallery.blade.php` ✅
- `resources/views/admin/projects/edit.blade.php` ✅
- `resources/views/admin/projects/create.blade.php` ✅

## Artisan Commands

### Create Symlink
```bash
cd public_html/repositories/cms
php artisan storage:link
```

**Verifies**: `ls -la public/storage` should show `→ ../storage/app/public`

### If Symlink Fails (cPanel restriction)
```bash
cd public_html/repositories/cms/public
ln -s ../storage/app/public storage
```

## URL Format

| Component | Value |
|-----------|-------|
| Base Path | `/repositories/cms` |
| Storage Path | `/storage` |
| File Path | `projects/photos/filename.jpg` |
| **Final URL** | `/repositories/cms/storage/projects/photos/filename.jpg` |

## Testing

1. **Upload test image** via gallery
2. **Check storage**: `ls -la storage/app/public/projects/photos/`
3. **Check symlink**: `ls -la public/storage`
4. **Test URL**: `https://jbtech.com.np/repositories/cms/storage/projects/photos/[filename]`
5. **Check gallery**: Images should load

## Fallback (No Symlink)

If symlinks are disabled, create `.htaccess` in `public/storage/`:

```apache
RewriteEngine On
RewriteRule ^(.*)$ ../../storage/app/public/$1 [L]
```

Or use direct path in StorageHelper (not recommended for security).

## Files Modified

- ✅ `app/Helpers/StorageHelper.php` - Fixed URL generation
- ✅ `config/filesystems.php` - Updated public disk URL
- ✅ `resources/views/admin/projects/gallery.blade.php` - Uses StorageHelper
- ✅ `resources/views/admin/projects/edit.blade.php` - Uses StorageHelper
- ✅ `resources/views/admin/projects/create.blade.php` - Fixed JavaScript URL

## Verification

Run in tinker:
```php
php artisan tinker
>>> \App\Helpers\StorageHelper::url('projects/photos/test.jpg')
=> "http://localhost/repositories/cms/storage/projects/photos/test.jpg"
```

