# Use the official PHP image with Apache
FROM php:8.2-apache

# Enable mod_rewrite and SSL modules
RUN a2enmod rewrite
RUN a2enmod ssl
RUN a2enmod headers

# Allow .htaccess overrides
RUN sed -i 's/AllowOverride None/AllowOverride All/g' /etc/apache2/apache2.conf

# Install system dependencies for composer (zip, unzip, git)
RUN apt-get update && apt-get install -y \
    zip \
    unzip \
    git \
    openssl \
 && rm -rf /var/lib/apt/lists/*

# Install Composer globally
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Copy project files to the Apache document root
COPY . /var/www/html/

# Copy SSL configuration
COPY apache-ssl.conf /etc/apache2/sites-available/000-default.conf

# Create SSL directory
RUN mkdir -p /etc/apache2/ssl

# Set proper permissions
RUN chown -R www-data:www-data /var/www/html

# Expose ports 80 and 443
EXPOSE 80 443
