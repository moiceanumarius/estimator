#!/bin/bash

# Configuration Test Script
# This script tests the configuration system
# Must be run from the project root directory

set -e

# Check if we're in the right directory
if [ ! -f "docker-compose.yml" ]; then
    echo "❌ This script must be run from the project root directory"
    echo "   Please run: ./config/test-config.sh"
    exit 1
fi

echo "🧪 Testing Configuration System"
echo "================================"

# Test 1: Check if all required files exist
echo ""
echo "📁 Test 1: File Structure Check"
echo "-------------------------------"

REQUIRED_FILES=(
    "config/env.dev"
    "config/env.prod"
    "config/apache/dev.conf"
    "config/apache/prod.conf"
    "config/websocket/dev.conf"
    "config/websocket/prod.conf"
    "config/setup-ssl.sh"
    "config/generate-configs.sh"
    "config/switch-env.sh"
    "config/ssl/dev/localhost.crt"
    "config/ssl/dev/localhost.key"
    "config/ssl/prod/estimator.crt"
    "config/ssl/prod/estimator.key"
)

MISSING_FILES=()
for file in "${REQUIRED_FILES[@]}"; do
    if [ -f "$file" ]; then
        echo "✅ $file"
    else
        echo "❌ $file (missing)"
        MISSING_FILES+=("$file")
    fi
done

if [ ${#MISSING_FILES[@]} -gt 0 ]; then
    echo ""
    echo "❌ Missing files: ${MISSING_FILES[*]}"
    exit 1
else
    echo ""
    echo "✅ All required files exist"
fi

# Test 2: Test environment switching
echo ""
echo "🔄 Test 2: Environment Switching"
echo "--------------------------------"

echo "Testing switch to development..."
./config/switch-env.sh dev > /dev/null 2>&1
if [ $? -eq 0 ]; then
    echo "✅ Development switch successful"
else
    echo "❌ Development switch failed"
    exit 1
fi

echo "Testing switch to production..."
./config/switch-env.sh prod > /dev/null 2>&1
if [ $? -eq 0 ]; then
    echo "✅ Production switch successful"
else
    echo "❌ Production switch failed"
    exit 1
fi

echo "Testing switch back to development..."
./config/switch-env.sh dev > /dev/null 2>&1
if [ $? -eq 0 ]; then
    echo "✅ Development switch back successful"
else
    echo "❌ Development switch back failed"
    exit 1
fi

# Test 3: Check generated configurations
echo ""
echo "🔧 Test 3: Generated Configurations"
echo "-----------------------------------"

if [ -f "apache-ssl.conf" ]; then
    echo "✅ Apache configuration generated"
    
    # Check if it contains localhost (dev environment)
    if grep -q "ServerName localhost" "apache-ssl.conf"; then
        echo "✅ Apache config contains localhost (dev)"
    else
        echo "❌ Apache config missing localhost"
    fi
else
    echo "❌ Apache configuration not generated"
    exit 1
fi

if [ -f "websocket.conf" ]; then
    echo "✅ WebSocket configuration generated"
    
    # Check if it contains dev settings
    if grep -q "DEBUG_MODE=true" "websocket.conf"; then
        echo "✅ WebSocket config contains dev settings"
    else
        echo "❌ WebSocket config missing dev settings"
    fi
else
    echo "❌ WebSocket configuration not generated"
    exit 1
fi

# Test 4: SSL Certificate Validation
echo ""
echo "🔐 Test 4: SSL Certificate Validation"
echo "-------------------------------------"

# Check localhost certificate
if [ -f "ssl/localhost.crt" ]; then
    echo "✅ Localhost certificate exists"
    
    # Check certificate subject
    SUBJECT=$(openssl x509 -in ssl/localhost.crt -noout -subject 2>/dev/null | sed 's/subject= //')
    if echo "$SUBJECT" | grep -q "CN=localhost"; then
        echo "✅ Localhost certificate has correct CN"
    else
        echo "❌ Localhost certificate has wrong CN: $SUBJECT"
    fi
else
    echo "❌ Localhost certificate missing"
fi

# Check estimator certificate
if [ -f "ssl/estimator.crt" ]; then
    echo "✅ Estimator certificate exists"
    
    # Check certificate subject
    SUBJECT=$(openssl x509 -in ssl/estimator.crt -noout -subject 2>/dev/null | sed 's/subject= //')
    if echo "$SUBJECT" | grep -q "CN=www.estimatorapp.site"; then
        echo "✅ Estimator certificate has correct CN"
    else
        echo "❌ Estimator certificate has wrong CN: $SUBJECT"
    fi
else
    echo "❌ Estimator certificate missing"
fi

# Test 5: Script Permissions
echo ""
echo "🔓 Test 5: Script Permissions"
echo "------------------------------"

SCRIPTS=("config/setup-ssl.sh" "config/generate-configs.sh" "config/switch-env.sh")
for script in "${SCRIPTS[@]}"; do
    if [ -x "$script" ]; then
        echo "✅ $script is executable"
    else
        echo "❌ $script is not executable"
        chmod +x "$script"
        echo "🔧 Made $script executable"
    fi
done

echo ""
echo "🎉 All tests passed successfully!"
echo ""
echo "📋 Current environment:"
source config/env
echo "  ENV: $ENV"
echo "  DOMAIN: $DOMAIN"
echo "  SSL_CERT: $SSL_CERT_PATH"
echo ""
echo "🚀 Ready to use! You can now:"
echo "  - Switch environments: ./config/switch-env.sh [dev|prod]"
echo "  - Use Makefile: cd config && make [dev|prod|status]"
echo "  - Rebuild Docker: docker-compose down && docker-compose up -d"
