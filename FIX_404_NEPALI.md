# 404 Error Fix - Nepali Guide

## समस्या
Symlink बनेको छ तर 404 error आउँछ।

## मुख्य कारणहरू

### 1. Document Root Configuration
cPanel मा Document Root check गर्नुहोस्:

**यदि Document Root = `jbtechco/repositories/cms/public`** (सही)
- URL format: `/storage/projects/photos/filename.jpg`
- Full URL: `https://jbtech.com.np/storage/projects/photos/filename.jpg`

**यदि Document Root = `jbtechco/repositories/cms`** (गलत)
- URL format: `/repositories/cms/storage/projects/photos/filename.jpg`
- Full URL: `https://jbtech.com.np/repositories/cms/storage/projects/photos/filename.jpg`

### 2. .htaccess Update गर्नुहोस्

```bash
cd jbtechco/repositories/cms/public
nano .htaccess
```

**यो line add गर्नुहोस्:**
```apache
Options +FollowSymLinks
```

### 3. Permissions Fix

```bash
cd jbtechco/repositories/cms
chmod -R 755 storage
chmod -R 755 public
chmod -R 755 public/storage
```

## Quick Fix Commands

```bash
cd jbtechco/repositories/cms

# 1. Permissions fix
chmod -R 755 storage public public/storage

# 2. Check symlink
ls -la public/storage

# 3. Check file via symlink
ls -la public/storage/projects/photos/

# 4. Test URL (Document Root public मा छ भने)
# https://jbtech.com.np/storage/projects/photos/wLJX1kEUkeWYToWsZuk9fRO3eFPdSPswli90Gvls.jpg
```

## Document Root Check गर्ने तरिका

cPanel मा:
1. File Manager खोल्नुहोस्
2. Domain settings मा जानुहोस्
3. Document Root check गर्नुहोस्
4. Should be: `jbtechco/repositories/cms/public`

## यदि Document Root `public` मा छ भने

StorageHelper को URL format change गर्नुपर्छ:
- Current: `/repositories/cms/storage/...`
- Should be: `/storage/...`

## Testing

1. Browser मा जानुहोस्:
   - `https://jbtech.com.np/storage/projects/photos/wLJX1kEUkeWYToWsZuk9fRO3eFPdSPswli90Gvls.jpg`
   - वा
   - `https://jbtech.com.np/repositories/cms/storage/projects/photos/wLJX1kEUkeWYToWsZuk9fRO3eFPdSPswli90Gvls.jpg`

2. कुन URL काम गर्छ त्यो check गर्नुहोस्

3. त्यस अनुसार StorageHelper update गर्नुहोस्

