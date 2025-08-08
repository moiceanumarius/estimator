#!/bin/bash

# SSL Setup Script for Development and Production Environments
# This script sets up SSL certificates for both environments

set -e

echo "🔐 Setting up SSL certificates for development and production environments..."

# Create SSL directories if they don't exist
mkdir -p config/ssl/dev
mkdir -p config/ssl/prod

# Copy existing certificates to appropriate directories
if [ -f "ssl/localhost.crt" ] && [ -f "ssl/localhost.key" ]; then
    echo "📋 Copying localhost certificates to dev environment..."
    cp ssl/localhost.crt config/ssl/dev/
    cp ssl/localhost.key config/ssl/dev/
    chmod 644 config/ssl/dev/localhost.crt
    chmod 600 config/ssl/dev/localhost.key
fi

if [ -f "ssl/estimator.crt" ] && [ -f "ssl/estimator.key" ]; then
    echo "📋 Copying estimator certificates to prod environment..."
    cp ssl/estimator.crt config/ssl/prod/
    cp ssl/estimator.key config/ssl/prod/
    chmod 644 config/ssl/prod/estimator.crt
    chmod 600 config/ssl/prod/estimator.key
fi

# Generate new localhost certificate for dev if it doesn't exist
if [ ! -f "config/ssl/dev/localhost.crt" ] || [ ! -f "config/ssl/dev/localhost.key" ]; then
    echo "🔑 Generating new localhost SSL certificate for development..."
    openssl req -x509 -newkey rsa:2048 \
        -keyout config/ssl/dev/localhost.key \
        -out config/ssl/dev/localhost.crt \
        -days 365 -nodes \
        -subj "/C=RO/ST=Bucharest/L=Bucharest/O=Localhost/OU=IT/CN=localhost" \
        -addext "subjectAltName=DNS:localhost,DNS:127.0.0.1,IP:127.0.0.1,IP:::1"
    
    chmod 644 config/ssl/dev/localhost.crt
    chmod 600 config/ssl/dev/localhost.key
    echo "✅ Localhost certificate generated successfully!"
fi

# Generate new estimator certificate for prod if it doesn't exist
if [ ! -f "config/ssl/prod/estimator.crt" ] || [ ! -f "config/ssl/prod/estimator.key" ]; then
    echo "🔑 Generating new estimator SSL certificate for production..."
    openssl req -x509 -newkey rsa:2048 \
        -keyout config/ssl/prod/estimator.key \
        -out config/ssl/prod/estimator.crt \
        -days 365 -nodes \
        -subj "/C=RO/ST=Bucharest/L=Bucharest/O=Estimator/OU=IT/CN=www.estimatorapp.site" \
        -addext "subjectAltName=DNS:www.estimatorapp.site,DNS:estimatorapp.site"
    
    chmod 644 config/ssl/prod/estimator.crt
    chmod 600 config/ssl/prod/estimator.key
    echo "✅ Estimator certificate generated successfully!"
fi

echo "🔐 SSL setup completed successfully!"
echo ""
echo "📁 Certificate locations:"
echo "   Development: config/ssl/dev/"
echo "   Production:  config/ssl/prod/"
echo ""
echo "📋 To switch environments, copy the appropriate env file:"
echo "   Development: cp config/env.dev config/env"
echo "   Production:  cp config/env.prod config/env"
