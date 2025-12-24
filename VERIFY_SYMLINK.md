# Verify Storage Symlink

## Check 1: See if it's a symlink (without trailing slash)
```bash
ls -la public/storage
```

**Should show:**
```
lrwxrwxrwx ... public/storage -> ../storage/app/public
```

## Check 2: Check if storage/app/public has files
```bash
ls -la storage/app/public
```

**Should show directories like:**
```
projects/
companies/
expenses/
```

## Check 3: Check projects/photos directory
```bash
ls -la storage/app/public/projects/photos
```

**Should show your image files**

## If storage/app/public is empty

The symlink is working, but you need to ensure files are uploaded to `storage/app/public/` not `public/storage/`.

Files should be stored in: `storage/app/public/projects/photos/`
They will be accessible via: `public/storage/projects/photos/` (through symlink)

