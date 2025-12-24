# Fix Existing Storage Directory

## Problem
- `public/storage` exists as a **directory** (not a symlink)
- Need to remove it and create a **symlink** instead

## Solution

### Step 1: Backup existing files (if needed)
```bash
cd jbtechco/repositories/cms
cp -r public/storage public/storage_backup
```

### Step 2: Remove existing directory
```bash
rm -rf public/storage
```

### Step 3: Create symlink
```bash
php artisan storage:link
```

### Step 4: Verify symlink
```bash
ls -la public/storage
```

**Should show:**
```
lrwxrwxrwx ... public/storage -> ../storage/app/public
```

### Step 5: Move files from backup (if needed)
```bash
# If you had files in public/storage, move them to storage/app/public
cp -r public/storage_backup/* storage/app/public/
rm -rf public/storage_backup
```

## Complete Command Sequence

```bash
cd jbtechco/repositories/cms

# Remove existing directory
rm -rf public/storage

# Create symlink
php artisan storage:link

# Verify
ls -la public/storage
```

## Alternative: Manual Symlink

If `php artisan storage:link` still fails:

```bash
cd jbtechco/repositories/cms/public
ln -s ../storage/app/public storage
ls -la storage
```

## Important Notes

- The existing `public/storage` directory will be **deleted**
- Files should be in `storage/app/public/` (not `public/storage/`)
- After creating symlink, files in `storage/app/public/` will be accessible via `public/storage/`

