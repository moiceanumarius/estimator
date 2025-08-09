#!/bin/bash

# Configuration Generator Script
# This script generates Apache and WebSocket configurations from templates

set -e

# Load environment configuration
if [ -f "config/env" ]; then
    echo "📋 Loading environment configuration from config/env..."
    set -a  # automatically export all variables
    source config/env
    set +a  # turn off automatic export
else
    echo "❌ Environment file config/env not found!"
    echo "Please copy config/env.dev or config/env.prod to config/env first."
    exit 1
fi

echo "🔧 Generating configurations for environment: $ENV"
echo "   Domain: $DOMAIN"
echo "   SSL Cert: $SSL_CERT_PATH"
echo "   SSL Key: $SSL_KEY_PATH"

# Function to replace environment variables in a file
replace_env_vars() {
    local input_file="$1"
    local output_file="$2"
    
    if [ ! -f "$input_file" ]; then
        echo "❌ Template file $input_file not found!"
        return 1
    fi
    
    # Replace environment variables
    envsubst < "$input_file" > "$output_file"
    echo "✅ Generated $output_file"
}

# Generate Apache configuration
echo "🌐 Generating Apache configuration..."
if [ "$ENV" = "dev" ]; then
    replace_env_vars "config/apache/dev.conf" "apache-ssl.conf"
elif [ "$ENV" = "prod" ]; then
    replace_env_vars "config/apache/prod.conf" "apache-ssl.conf"
else
    echo "❌ Unknown environment: $ENV"
    exit 1
fi

# Generate WebSocket configuration
echo "🔌 Generating WebSocket configuration..."
if [ "$ENV" = "dev" ]; then
    replace_env_vars "config/websocket/dev.conf" "websocket.conf"
elif [ "$ENV" = "prod" ]; then
    replace_env_vars "config/websocket/prod.conf" "websocket.conf"
fi

# Copy SSL certificates to Docker container location
echo "📋 Copying SSL certificates..."
if [ "$ENV" = "dev" ]; then
    cp "config/ssl/dev/localhost.crt" "ssl/localhost.crt"
    cp "config/ssl/dev/localhost.key" "ssl/localhost.key"
elif [ "$ENV" = "prod" ]; then
    cp "config/ssl/prod/estimator.crt" "ssl/estimator.crt"
    cp "config/ssl/prod/estimator.key" "ssl/estimator.key"
fi

echo "✅ Configuration generation completed successfully!"
echo ""
echo "📁 Generated files:"
echo "   Apache: apache-ssl.conf"
echo "   WebSocket: websocket.conf"
echo "   SSL: ssl/"
echo ""
echo "🚀 You can now rebuild your Docker containers:"
echo "   docker-compose down"
echo "   docker-compose up -d"
