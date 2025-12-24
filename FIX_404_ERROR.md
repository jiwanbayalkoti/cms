# Fix 404 Error - Step by Step

## Problem
Symlink बनेको छ तर 404 error आउँछ।

## Solutions

### Solution 1: Check .htaccess in public directory

```bash
cd jbtechco/repositories/cms/public
cat .htaccess
```

**यदि .htaccess छैन भने, बनाउनुहोस्:**

```apache
<IfModule mod_rewrite.c>
    <IfModule mod_negotiation.c>
        Options -MultiViews -Indexes
    </IfModule>

    RewriteEngine On

    # Handle Authorization Header
    RewriteCond %{HTTP:Authorization} .
    RewriteRule .* - [E=HTTP_AUTHORIZATION:%{HTTP:Authorization}]

    # Redirect Trailing Slashes If Not A Folder...
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteCond %{REQUEST_URI} (.+)/$
    RewriteRule ^ %1 [L,R=301]

    # Send Requests To Front Controller...
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteRule ^ index.php [L]
    
    # Allow symlinks
    Options +FollowSymLinks
</IfModule>
```

### Solution 2: Check Document Root

cPanel मा Document Root check गर्नुहोस्:
- Should point to: `jbtechco/repositories/cms/public`
- NOT: `jbtechco/repositories/cms`

### Solution 3: Check Permissions

```bash
cd jbtechco/repositories/cms
chmod -R 755 storage
chmod -R 755 public
chmod -R 755 public/storage
```

### Solution 4: Test Direct Access

```bash
# Check if file exists via symlink
ls -la public/storage/projects/photos/wLJX1kEUkeWYToWsZuk9fRO3eFPdSPswli90Gvls.jpg

# Check actual file
ls -la storage/app/public/projects/photos/wLJX1kEUkeWYToWsZuk9fRO3eFPdSPswli90Gvls.jpg
```

### Solution 5: Check URL Path

Current URL format:
```
https://jbtech.com.np/repositories/cms/storage/projects/photos/filename.jpg
```

यदि Document Root `public` मा point गरेको छ भने, URL हो:
```
https://jbtech.com.np/storage/projects/photos/filename.jpg
```

## Quick Diagnostic

```bash
cd jbtechco/repositories/cms

# 1. Check symlink
ls -la public/storage

# 2. Check file via symlink
ls -la public/storage/projects/photos/

# 3. Check permissions
stat public/storage
stat storage/app/public/projects/photos/

# 4. Check .htaccess
cat public/.htaccess | grep FollowSymLinks
```

## Most Common Issue

Document Root गलत directory मा point गरेको हुन सक्छ।

**Check गर्नुहोस्:**
- cPanel → File Manager → Document Root
- Should be: `jbtechco/repositories/cms/public`
- NOT: `jbtechco/repositories/cms`

