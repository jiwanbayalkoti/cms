# Create Storage Link - Step by Step

## Error: "could not open input file: artisan"

This means you're not in the Laravel project root directory.

## Solution

### Step 1: Navigate to Laravel Root

```bash
cd jbtechco/repositories/cms
```

**Verify you're in the right place:**
```bash
pwd
# Should show: /home/username/jbtechco/repositories/cms
# or similar path ending in /repositories/cms

ls -la
# Should show: artisan, app, config, public, storage, etc.
```

### Step 2: Verify artisan file exists

```bash
ls -la artisan
# Should show: -rwxr-xr-x ... artisan
```

### Step 3: Run storage:link

```bash
php artisan storage:link
```

**Expected output:**
```
The [public/storage] link has been connected to [storage/app/public].
The links have been created.
```

### Step 4: Verify symlink created

```bash
ls -la public/storage
# Should show: lrwxrwxrwx ... public/storage -> ../storage/app/public
```

## Complete Command Sequence

```bash
# Navigate to project root
cd ~/jbtechco/repositories/cms

# Or if using full path:
cd /home/yourusername/jbtechco/repositories/cms

# Verify location
pwd
ls artisan

# Create symlink
php artisan storage:link

# Verify
ls -la public/storage
```

## Alternative: Manual Symlink

If `php artisan storage:link` still fails:

```bash
cd ~/public_html/repositories/cms/public
ln -s ../storage/app/public storage
ls -la storage
```

## Troubleshooting

### If "artisan" file doesn't exist:
- You're in the wrong directory
- Navigate to: `jbtechco/repositories/cms`
- Should contain: `artisan`, `app`, `config`, `public`, `storage`

### If permission denied:
```bash
chmod +x artisan
php artisan storage:link
```

### If symlink creation fails:
- Check if `public/storage` already exists (remove it first)
- Check directory permissions
- Try manual symlink method above

