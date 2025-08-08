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
    openssl \
    supervisor

# Clear cache
RUN apt-get clean && rm -rf /var/lib/apt/lists/*

# Install PHP extensions
RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd

# Install Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Enable Apache modules
RUN a2enmod ssl
RUN a2enmod headers
RUN a2enmod rewrite
RUN a2enmod mime
# Enable proxy modules for WSS reverse proxy
RUN a2enmod proxy proxy_http proxy_wstunnel

# Allow .htaccess overrides
RUN sed -i 's/AllowOverride None/AllowOverride All/g' /etc/apache2/apache2.conf

# Create SSL directory
RUN mkdir -p /etc/apache2/ssl

# Copy composer files first
COPY composer.json composer.lock* /var/www/html/

# Set permissions and install dependencies
RUN chown -R www-data:www-data /var/www/html && \
    cd /var/www/html && \
    composer install --no-dev --optimize-autoloader --no-interaction

# Copy application files
COPY . /var/www/html/

# Set permissions again
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

# Create supervisor configuration for WebSocket
RUN echo '[program:websocket]' > /etc/supervisor/conf.d/websocket.conf && \
    echo 'command=php /var/www/html/websocket-server.php' >> /etc/supervisor/conf.d/websocket.conf && \
    echo 'directory=/var/www/html' >> /etc/supervisor/conf.d/websocket.conf && \
    echo 'autostart=true' >> /etc/supervisor/conf.d/websocket.conf && \
    echo 'autorestart=true' >> /etc/supervisor/conf.d/websocket.conf && \
    echo 'stderr_logfile=/var/log/websocket.err.log' >> /etc/supervisor/conf.d/websocket.conf && \
    echo 'stdout_logfile=/var/log/websocket.out.log' >> /etc/supervisor/conf.d/websocket.conf

# Expose ports
EXPOSE 80 443 8080

# Copy Apache configuration
COPY apache-ssl.conf /etc/apache2/sites-available/000-default.conf

# Copy startup script
COPY docker-start.sh /usr/local/bin/
RUN chmod +x /usr/local/bin/docker-start.sh

# Start services
CMD ["/usr/local/bin/docker-start.sh"]
