#!/bin/bash

# Generate SSL Certificate Script for Estimator App

echo "Generating SSL certificates for Estimator app..."

# Create SSL directory
mkdir -p ssl

# Generate private key
echo "Generating private key..."
openssl genrsa -out ssl/estimator.key 2048

# Generate certificate signing request for www.estimatorapp.site
echo "Generating certificate signing request for www.estimatorapp.site..."
openssl req -new -key ssl/estimator.key -out ssl/estimator.csr -subj "/C=RO/ST=Bucharest/L=Bucharest/O=Estimator/OU=IT/CN=www.estimatorapp.site"

# Generate self-signed certificate
echo "Generating self-signed certificate..."
openssl x509 -req -days 365 -in ssl/estimator.csr -signkey ssl/estimator.key -out ssl/estimator.crt

# Set proper permissions
chmod 600 ssl/estimator.key
chmod 644 ssl/estimator.crt

echo "SSL certificates generated successfully!"
echo "Files created:"
echo "  - ssl/estimator.key (private key)"
echo "  - ssl/estimator.crt (certificate)"
echo "  - ssl/estimator.csr (certificate signing request)"
echo ""
echo "Certificate details:"
echo "  - Domain: www.estimatorapp.site"
echo "  - Valid for: 365 days"
echo "  - Type: Self-signed (development)"
echo ""
echo "Note: This is a self-signed certificate for development."
echo "For production, use a certificate from a trusted CA."
