# Use the official PHP image with Apache
FROM php:8.2-apache

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    openssl

# Clear cache
RUN apt-get clean && rm -rf /var/lib/apt/lists/*

# Install PHP extensions
RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd

# Enable Apache modules
RUN a2enmod ssl
RUN a2enmod headers
RUN a2enmod rewrite
RUN a2enmod mime

# Allow .htaccess overrides
RUN sed -i 's/AllowOverride None/AllowOverride All/g' /etc/apache2/apache2.conf

# Create SSL directory
RUN mkdir -p /etc/apache2/ssl

# Copy application files
COPY . /var/www/html/

# Set permissions
RUN chown -R www-data:www-data /var/www/html

# Create Apache MIME configuration
RUN echo '<IfModule mod_mime.c>' > /etc/apache2/conf-available/mime-types.conf && \
    echo '    AddType text/css .css' >> /etc/apache2/conf-available/mime-types.conf && \
    echo '    AddType application/javascript .js' >> /etc/apache2/conf-available/mime-types.conf && \
    echo '    AddType text/javascript .js' >> /etc/apache2/conf-available/mime-types.conf && \
    echo '    AddType image/svg+xml .svg' >> /etc/apache2/conf-available/mime-types.conf && \
    echo '    AddType application/json .json' >> /etc/apache2/conf-available/mime-types.conf && \
    echo '</IfModule>' >> /etc/apache2/conf-available/mime-types.conf

# Enable MIME configuration
RUN a2enconf mime-types

# Expose ports
EXPOSE 80 443

# Copy Apache configuration
COPY apache-ssl.conf /etc/apache2/sites-available/000-default.conf

# Start Apache
CMD ["apache2-foreground"]
