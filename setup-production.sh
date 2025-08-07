#!/bin/bash

# Production Setup Script for Estimator App
# Run this script on the Linux Alma server

echo "Setting up Estimator app for production..."

# Navigate to project directory
cd /var/www/estimator

# Install Composer dependencies
echo "Installing Composer dependencies..."
composer install --no-dev --optimize-autoloader

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

echo "Setup complete! The app should now work correctly."
echo "Make sure Apache is configured to serve from /var/www/estimator"
