# Test Image Access

## Your Photo File
- **File**: `wLJX1kEUkeWYToWsZuk9fRO3eFPdSPswli90Gvls.jpg`
- **Location**: `storage/app/public/projects/photos/`

## Verify Symlink Works

```bash
# Check symlink (should show -> ../storage/app/public)
ls -la public/storage

# Check if file is accessible via symlink
ls -la public/storage/projects/photos/
```

**Should show the same file through symlink**

## Test URL Access

Your image should be accessible at:
```
https://jbtech.com.np/repositories/cms/storage/projects/photos/wLJX1kEUkeWYToWsZuk9fRO3eFPdSPswli90Gvls.jpg
```

## Test in Browser

1. Open browser
2. Go to: `https://jbtech.com.np/repositories/cms/storage/projects/photos/wLJX1kEUkeWYToWsZuk9fRO3eFPdSPswli90Gvls.jpg`
3. Image should display (not 404)

## Test Gallery Page

1. Go to: `https://jbtech.com.np/repositories/cms/admin/projects/1/gallery`
2. Check browser console (F12) for any 404 errors
3. Images should load correctly

## If Still 404

Check:
1. Web server permissions
2. .htaccess in public directory
3. APP_URL in .env file

