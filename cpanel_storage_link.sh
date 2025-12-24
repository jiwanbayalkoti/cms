#!/bin/bash

# cPanel Storage Link Setup Script
# For Laravel project in: jbtechco/repositories/cms

echo "=========================================="
echo "cPanel Laravel Storage Link Setup"
echo "=========================================="
echo ""

# Change to project directory
# Try common paths
PROJECT_DIR=""

# Check if we're already in the project root
if [ -f "artisan" ]; then
    PROJECT_DIR="$(pwd)"
    echo "Already in project root: $PROJECT_DIR"
else
    # Try different possible paths
    if [ -d "jbtechco/repositories/cms" ]; then
        PROJECT_DIR="jbtechco/repositories/cms"
    elif [ -d "~/jbtechco/repositories/cms" ]; then
        PROJECT_DIR="~/jbtechco/repositories/cms"
    elif [ -d "/home/$(whoami)/jbtechco/repositories/cms" ]; then
        PROJECT_DIR="/home/$(whoami)/jbtechco/repositories/cms"
    elif [ -d "public_html/repositories/cms" ]; then
        PROJECT_DIR="public_html/repositories/cms"
    else
        echo "Error: Cannot find Laravel project directory!"
        echo "Please run this script from one of these locations:"
        echo "  - Inside the Laravel project root (where 'artisan' file exists)"
        echo "  - In the directory containing 'jbtechco/repositories/cms'"
        echo ""
        echo "Current directory: $(pwd)"
        echo ""
        echo "Or manually set PROJECT_DIR in this script to your project path."
        exit 1
    fi
    
    if [ ! -d "$PROJECT_DIR" ]; then
        echo "Error: Project directory '$PROJECT_DIR' not found!"
        echo "Current directory: $(pwd)"
        echo "Please navigate to the correct directory or update PROJECT_DIR in the script."
        exit 1
    fi
    
    cd "$PROJECT_DIR" || exit 1
fi

echo "Current directory: $(pwd)"
echo ""

# Verify we're in the right place
if [ ! -f "artisan" ]; then
    echo "Error: 'artisan' file not found in current directory!"
    echo "Please navigate to the Laravel project root directory."
    exit 1
fi

echo "✓ Found artisan file"
echo ""

# Check if storage/app/public exists
if [ ! -d "storage/app/public" ]; then
    echo "Creating storage/app/public directory..."
    mkdir -p storage/app/public
    mkdir -p storage/app/public/projects/photos
    mkdir -p storage/app/public/projects/files
fi

# Remove existing symlink if it exists
if [ -L "public/storage" ] || [ -e "public/storage" ]; then
    echo "Removing existing storage link..."
    rm -f public/storage
fi

# Create the symlink using artisan
echo "Creating storage symlink..."
php artisan storage:link

# Verify the symlink
if [ -L "public/storage" ]; then
    echo ""
    echo "✓ Storage symlink created successfully!"
    echo "  Link: $(readlink -f public/storage)"
    echo ""
    
    # Set permissions
    echo "Setting permissions..."
    chmod -R 755 storage
    chmod -R 755 public/storage
    
    # Get current user (cPanel username)
    CURRENT_USER=$(whoami)
    echo "Setting ownership to: $CURRENT_USER"
    chown -R "$CURRENT_USER:$CURRENT_USER" storage 2>/dev/null || echo "  (Skipping ownership change)"
    chown -R "$CURRENT_USER:$CURRENT_USER" public/storage 2>/dev/null || echo "  (Skipping ownership change)"
    
    echo ""
    echo "=========================================="
    echo "Setup Complete!"
    echo "=========================================="
    echo ""
    echo "Storage structure:"
    echo "  Files stored in: storage/app/public/projects/photos/"
    echo "  Symlink created: public/storage -> ../storage/app/public"
    echo ""
    echo "Test your photos at:"
    echo "  https://yourdomain.com/repositories/cms/storage/projects/photos/[filename]"
    echo ""
    echo "Check gallery at:"
    echo "  https://yourdomain.com/repositories/cms/admin/projects/1/gallery"
    echo ""
else
    echo ""
    echo "✗ Error: Storage symlink was not created!"
    echo "Trying manual symlink creation..."
    
    # Try manual symlink
    cd public
    ln -s ../storage/app/public storage
    cd ..
    
    if [ -L "public/storage" ]; then
        echo "✓ Manual symlink created successfully!"
    else
        echo "✗ Manual symlink creation also failed!"
        echo "Please check permissions and try manually:"
        echo "  cd $PROJECT_DIR/public"
        echo "  ln -s ../storage/app/public storage"
        exit 1
    fi
fi

echo ""
echo "Verification:"
echo "  Symlink: $(ls -la public/storage | head -1)"
echo "  Storage directory: $(ls -d storage/app/public 2>/dev/null && echo 'exists' || echo 'missing')"
echo ""

