#!/bin/bash

# Environment Switch Script
# Usage: ./config/switch-env.sh [dev|prod]

set -e

if [ $# -eq 0 ]; then
    echo "🔧 Environment Switch Script"
    echo ""
    echo "Usage: $0 [dev|prod]"
    echo ""
    echo "Available environments:"
    echo "  dev  - Development (localhost)"
    echo "  prod - Production (estimatorapp.site)"
    echo ""
    echo "Current environment:"
    if [ -f "config/env" ]; then
        source config/env
        echo "  ENV: $ENV"
        echo "  DOMAIN: $DOMAIN"
    else
        echo "  No environment file found"
    fi
    exit 1
fi

ENVIRONMENT=$1

if [ "$ENVIRONMENT" != "dev" ] && [ "$ENVIRONMENT" != "prod" ]; then
    echo "❌ Invalid environment: $ENVIRONMENT"
    echo "Valid options: dev, prod"
    exit 1
fi

echo "🔄 Switching to $ENVIRONMENT environment..."

# Copy the appropriate environment file
if [ -f "config/env.$ENVIRONMENT" ]; then
    cp "config/env.$ENVIRONMENT" "config/env"
    echo "✅ Environment file updated"
else
    echo "❌ Environment file config/env.$ENVIRONMENT not found!"
    exit 1
fi

# Generate new configurations
echo "🔧 Generating new configurations..."
./config/generate-configs.sh

echo "✅ Successfully switched to $ENVIRONMENT environment!"
echo ""
echo "📋 Current configuration:"
source config/env
echo "  ENV: $ENV"
echo "  DOMAIN: $DOMAIN"
echo "  SSL_CERT: $SSL_CERT_PATH"
echo "  SSL_KEY: $SSL_KEY_PATH"
echo ""
echo "🚀 To apply changes, rebuild your Docker containers:"
echo "   docker-compose down"
echo "   docker-compose up -d"
