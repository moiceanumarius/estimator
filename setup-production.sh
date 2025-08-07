#!/bin/bash

# Production Setup Script for Estimator App
# Run this script on the Linux Alma server

echo "Setting up Estimator app for production..."

# Navigate to project directory
cd /var/www/estimator

# Check if composer.json exists
if [ ! -f "composer.json" ]; then
    echo "ERROR: composer.json not found in /var/www/estimator"
    echo "Make sure you cloned the repository correctly"
    exit 1
fi

# Check if Composer is installed
if ! command -v composer &> /dev/null; then
    echo "ERROR: Composer is not installed"
    echo "Please install Composer first: https://getcomposer.org/download/"
    exit 1
fi

# Install Composer dependencies
echo "Installing Composer dependencies..."
composer install --no-dev --optimize-autoloader

# Check if autoload.php was created
if [ ! -f "vendor/autoload.php" ]; then
    echo "ERROR: vendor/autoload.php was not created"
    echo "Composer install failed. Please check the error messages above."
    exit 1
fi

echo "✓ Composer dependencies installed successfully"

# Set proper permissions
echo "Setting proper permissions..."
chown -R www-data:www-data /var/www/estimator
chmod -R 755 /var/www/estimator
chmod -R 777 /var/www/estimator/session

# Create session directory if it doesn't exist
mkdir -p /var/www/estimator/session
chmod 777 /var/www/estimator/session

# Generate autoload files
echo "Generating autoload files..."
composer dump-autoload --optimize

# Final verification
echo "Verifying installation..."
if [ -f "vendor/autoload.php" ]; then
    echo "✓ Autoloader found at vendor/autoload.php"
else
    echo "✗ ERROR: Autoloader not found"
    exit 1
fi

if [ -f "index.php" ]; then
    echo "✓ index.php found"
else
    echo "✗ ERROR: index.php not found"
    exit 1
fi

if [ -d "classes" ]; then
    echo "✓ Classes directory found"
else
    echo "✗ ERROR: Classes directory not found"
    exit 1
fi

echo ""
echo "🎉 Setup complete! The app should now work correctly."
echo "Make sure Apache is configured to serve from /var/www/estimator"
echo ""
echo "Next steps:"
echo "1. Configure Apache (see DEPLOYMENT.md)"
echo "2. Test the application in your browser"
echo "3. Check logs if there are any issues"
