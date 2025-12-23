#!/bin/bash

# Deployment script to create storage symlink for production
# Run this script on your production server

echo "=========================================="
echo "Laravel Storage Link Deployment Script"
echo "=========================================="
echo ""

# Change to project directory
PROJECT_DIR="repositories/cms"

if [ ! -d "$PROJECT_DIR" ]; then
    echo "Error: Project directory '$PROJECT_DIR' not found!"
    echo "Please update PROJECT_DIR variable in this script to match your server path."
    exit 1
fi

cd "$PROJECT_DIR" || exit 1

echo "Current directory: $(pwd)"
echo ""

# Check if storage/app/public exists
if [ ! -d "storage/app/public" ]; then
    echo "Creating storage/app/public directory..."
    mkdir -p storage/app/public
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
    
    # Try to set ownership (may require sudo)
    if [ -w "storage" ]; then
        echo "Setting ownership (if needed)..."
        chown -R www-data:www-data storage 2>/dev/null || echo "  (Skipping ownership change - may require sudo)"
        chown -R www-data:www-data public/storage 2>/dev/null || echo "  (Skipping ownership change - may require sudo)"
    fi
    
    echo ""
    echo "=========================================="
    echo "Deployment Complete!"
    echo "=========================================="
    echo ""
    echo "Test your photos at:"
    echo "  https://jbtech.com.np/storage/projects/photos/[filename]"
    echo ""
    echo "Check gallery at:"
    echo "  https://jbtech.com.np/admin/projects/1/gallery"
    echo ""
else
    echo ""
    echo "✗ Error: Storage symlink was not created!"
    echo "Please check the error messages above."
    exit 1
fi

