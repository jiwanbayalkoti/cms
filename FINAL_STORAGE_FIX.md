# Final Storage URL Fix - cPanel Shared Hosting

## Problem Solved
- ❌ Wrong: `https://jbtech.com.np/projects/photos/filename.jpg` (404)
- ✅ Correct: `https://jbtech.com.np/repositories/cms/storage/projects/photos/filename.jpg`

## Exact Folder Structure Required

```
public_html/
└── repositories/
    └── cms/                          (Laravel root)
        ├── public/
        │   ├── index.php
        │   └── storage/              (SYMLINK → ../storage/app/public)
        ├── storage/
        │   └── app/
        │       └── public/
        │           └── projects/
        │               ├── photos/
        │               │   └── [image files]
        │               └── files/
        │                   └── [document files]
        ├── app/
        ├── config/
        │   └── filesystems.php       (UPDATED)
        └── routes/
```

## Artisan Commands

### 1. Create Storage Symlink
```bash
cd public_html/repositories/cms
php artisan storage:link
```

**Verify**:
```bash
ls -la public/storage
# Output: lrwxrwxrwx ... public/storage -> ../storage/app/public
```

### 2. Set Permissions
```bash
chmod -R 755 storage
chmod -R 755 public/storage
chown -R username:username storage public/storage
```

## Code Changes Applied

### 1. app/Helpers/StorageHelper.php
```php
public static function url(?string $path): ?string
{
    if (!$path) return null;
    $path = ltrim($path, '/');
    $basePath = '/repositories/cms';
    return url($basePath . '/storage/' . $path);
}
```

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

## Blade/PHP Examples

### ✅ Correct Usage
```blade
{{-- Gallery View --}}
@php
    $photoUrl = StorageHelper::url($photo['path']);
@endphp
<img src="{{ $photoUrl }}" alt="Photo">

{{-- Edit View --}}
@php
    $photoUrl = \App\Helpers\StorageHelper::url($photo['path']);
@endphp
<img src="{{ $photoUrl }}" alt="Photo">

{{-- File Links --}}
<a href="{{ \App\Helpers\StorageHelper::url($file['path']) }}">Download</a>
```

### ❌ Incorrect Usage (Removed)
```blade
{{-- DON'T USE --}}
<img src="{{ asset('storage/' . $path) }}">
<img src="/storage/{{ $path }}">
<img src="/projects/photos/{{ $path }}">
```

## Final Correct URL Format

| Component | Value |
|-----------|-------|
| Domain | `https://jbtech.com.np` |
| Base Path | `/repositories/cms` |
| Storage Path | `/storage` |
| File Path | `projects/photos/filename.jpg` |
| **Complete URL** | `https://jbtech.com.np/repositories/cms/storage/projects/photos/filename.jpg` |

## Files Updated

- ✅ `app/Helpers/StorageHelper.php` - Fixed URL generation with base path
- ✅ `config/filesystems.php` - Updated public disk URL config
- ✅ `resources/views/admin/projects/gallery.blade.php` - Uses StorageHelper
- ✅ `resources/views/admin/projects/edit.blade.php` - Uses StorageHelper
- ✅ `resources/views/admin/projects/create.blade.php` - Fixed JavaScript URL
- ✅ `resources/views/admin/projects/show.blade.php` - Uses StorageHelper

## Safe Fallback (If Symlink Not Allowed)

If `php artisan storage:link` fails due to cPanel restrictions:

### Option 1: Manual Symlink
```bash
cd public_html/repositories/cms/public
ln -s ../storage/app/public storage
```

### Option 2: .htaccess Rewrite (Not Recommended)
Create `public/storage/.htaccess`:
```apache
RewriteEngine On
RewriteRule ^(.*)$ ../../storage/app/public/$1 [L]
```

### Option 3: Direct Storage (Security Risk)
Modify StorageHelper to use direct path (bypasses symlink):
```php
// NOT RECOMMENDED - Security risk
return url('/repositories/cms/storage/app/public/' . $path);
```

## Testing Checklist

1. ✅ Run `php artisan storage:link`
2. ✅ Verify symlink: `ls -la public/storage`
3. ✅ Upload test image via gallery
4. ✅ Check file exists: `ls -la storage/app/public/projects/photos/`
5. ✅ Test URL: Visit `https://jbtech.com.np/repositories/cms/storage/projects/photos/[filename]`
6. ✅ Check gallery page loads images
7. ✅ Verify browser console has no 404 errors

## Verification Command

```bash
php artisan tinker
>>> \App\Helpers\StorageHelper::url('projects/photos/test.jpg')
=> "http://localhost/repositories/cms/storage/projects/photos/test.jpg"
```

## Notes

- All image URLs now include `/repositories/cms` base path
- StorageHelper is used consistently across all views
- No direct `asset('storage/...')` calls remain
- Works with standard Laravel storage symlink structure
- Compatible with cPanel shared hosting

