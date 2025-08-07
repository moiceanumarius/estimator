# Deployment Guide - Linux Alma Server

## Prerequisites

- Linux Alma server
- Apache 2.4+
- PHP 8.2+
- Composer
- Git

## Installation Steps

### 1. Install Composer (if not already installed)

```bash
# Download Composer installer
curl -sS https://getcomposer.org/installer | php

# Move to global location
sudo mv composer.phar /usr/local/bin/composer

# Make it executable
sudo chmod +x /usr/local/bin/composer

# Verify installation
composer --version
```

### 2. Clone the Repository

```bash
cd /var/www
git clone https://github.com/moiceanumarius/estimator.git
cd estimator
```

### 3. Run Setup Script

```bash
chmod +x setup-production.sh
./setup-production.sh
```

**Note**: The setup script will automatically:
- Install Composer dependencies
- Generate autoload files
- Set proper permissions
- Verify the installation

### 4. Configure Apache

#### Option A: Using the provided config file

```bash
# Copy the configuration file
sudo cp estimator.conf /etc/apache2/sites-available/estimator.conf

# Edit the file to match your domain
sudo nano /etc/apache2/sites-available/estimator.conf

# Enable the site
sudo a2ensite estimator.conf

# Disable default site (optional)
sudo a2dissite 000-default.conf

# Enable required modules
sudo a2enmod rewrite
sudo a2enmod php

# Restart Apache
sudo systemctl restart apache2
```

#### Option B: Manual configuration

Add this to your Apache virtual host configuration:

```apache
<VirtualHost *:80>
    ServerName your-domain.com
    DocumentRoot /var/www/estimator
    
    <Directory /var/www/estimator>
        Options Indexes FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
```

### 5. Set Permissions

```bash
sudo chown -R www-data:www-data /var/www/estimator
sudo chmod -R 755 /var/www/estimator
sudo chmod -R 777 /var/www/estimator/session
```

### 6. Test the Application

Visit your domain in a browser. You should see the Estimator login page.

## Troubleshooting

### Common Issues

1. **Autoloader not found**: 
   ```bash
   cd /var/www/estimator
   composer install --no-dev --optimize-autoloader
   ```

2. **Permission denied**: 
   ```bash
   sudo chown -R www-data:www-data /var/www/estimator
   sudo chmod -R 755 /var/www/estimator
   ```

3. **Apache 404**: Verify DocumentRoot in Apache configuration
4. **Session errors**: Ensure session directory is writable
5. **Composer not found**: Install Composer using the steps above

### Check Logs

```bash
# Apache error logs
sudo tail -f /var/log/apache2/error.log

# Application logs (if configured)
sudo tail -f /var/log/apache2/estimator_error.log
```

### Verify Installation

```bash
# Check PHP version
php -v

# Check Composer
composer --version

# Check Apache modules
apache2ctl -M | grep rewrite
apache2ctl -M | grep php

# Check if autoloader exists
ls -la /var/www/estimator/vendor/autoload.php

# Test PHP syntax
php -l /var/www/estimator/index.php
```

## Security Considerations

1. **File Permissions**: Ensure sensitive files are not publicly accessible
2. **Session Security**: Session files are stored in `/var/www/estimator/session/`
3. **HTTPS**: Consider enabling SSL/TLS for production use
4. **Firewall**: Configure firewall to allow only necessary ports

## Maintenance

### Update Application

```bash
cd /var/www/estimator
git pull origin main
composer install --no-dev --optimize-autoloader
sudo chown -R www-data:www-data /var/www/estimator
```

### Backup

```bash
# Backup application files
tar -czf estimator-backup-$(date +%Y%m%d).tar.gz /var/www/estimator

# Backup session data
tar -czf session-backup-$(date +%Y%m%d).tar.gz /var/www/estimator/session
```
