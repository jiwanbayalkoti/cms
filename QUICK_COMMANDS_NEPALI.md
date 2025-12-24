# Quick Commands - Nepali Guide

## सही Path
```
jbtechco/repositories/cms
```

## Storage Link बनाउने तरिका

### Step 1: Project directory मा जानुहोस्
```bash
cd jbtechco/repositories/cms
```

### Step 2: Verify गर्नुहोस् (artisan file छ कि छैन)
```bash
ls artisan
```

### Step 3: Storage link बनाउनुहोस्
```bash
php artisan storage:link
```

### Step 4: Check गर्नुहोस् (symlink बनेको छ कि छैन)
```bash
ls -la public/storage
```

**Output देखिनुपर्छ:**
```
lrwxrwxrwx ... public/storage -> ../storage/app/public
```

## Complete Command (एकै पटक)

```bash
cd jbtechco/repositories/cms && php artisan storage:link && ls -la public/storage
```

## यदि Error आयो भने

### "The [public/storage] link already exists" (तर directory छ)
यो मतलब `public/storage` directory को रूपमा छ, symlink होइन।

**समाधान:**
```bash
# 1. Existing directory हटाउनुहोस्
rm -rf public/storage

# 2. Symlink बनाउनुहोस्
php artisan storage:link

# 3. Verify गर्नुहोस्
ls -la public/storage
# Output: lrwxrwxrwx ... public/storage -> ../storage/app/public
```

### "artisan file not found"
- `jbtechco/repositories/cms` directory मा जानुहोस्
- `pwd` command ले current directory check गर्नुहोस्

### "Permission denied"
```bash
chmod +x artisan
php artisan storage:link
```

### Symlink बन्न सकेन भने (Manual)
```bash
cd jbtechco/repositories/cms/public
rm -rf storage  # पहिले existing directory हटाउनुहोस्
ln -s ../storage/app/public storage
ls -la storage
```

## Final URL Format

- **Wrong**: `https://jbtech.com.np/projects/photos/filename.jpg`
- **Correct**: `https://jbtech.com.np/repositories/cms/storage/projects/photos/filename.jpg`

## Testing

1. Gallery मा photo upload गर्नुहोस्
2. Browser console मा check गर्नुहोस् (404 error नहुनुपर्छ)
3. Direct URL test: `https://jbtech.com.np/repositories/cms/storage/projects/photos/[filename]`

