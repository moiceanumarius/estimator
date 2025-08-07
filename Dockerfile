# Use the official PHP image with Apache
FROM php:8.2-apache

# Enable mod_rewrite
RUN a2enmod rewrite

# Allow .htaccess overrides
RUN sed -i 's/AllowOverride None/AllowOverride All/g' /etc/apache2/apache2.conf

# Install system dependencies for composer (zip, unzip, git)
RUN apt-get update && apt-get install -y \
    zip \
    unzip \
    git \
 && rm -rf /var/lib/apt/lists/*

# Install Composer globally
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Copy project files to the Apache document root
COPY . /var/www/html/

# Expose port 80
EXPOSE 80
