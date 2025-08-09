#!/bin/bash

# Comprehensive Deployment Script
# Handles both development and production deployments

set -e

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Function to print colored output
print_status() {
    echo -e "${BLUE}[INFO]${NC} $1"
}

print_success() {
    echo -e "${GREEN}[SUCCESS]${NC} $1"
}

print_warning() {
    echo -e "${YELLOW}[WARNING]${NC} $1"
}

print_error() {
    echo -e "${RED}[ERROR]${NC} $1"
}

# Function to check if Docker is running
check_docker() {
    if ! docker info > /dev/null 2>&1; then
        print_error "Docker is not running or not accessible"
        print_status "Please start Docker and try again"
        exit 1
    fi
    print_success "Docker is running"
}

# Function to check if docker-compose is available
check_docker_compose() {
    if ! command -v docker-compose > /dev/null 2>&1; then
        print_error "docker-compose is not installed"
        print_status "Please install docker-compose and try again"
        exit 1
    fi
    print_success "docker-compose is available"
}

# Function to validate environment
validate_environment() {
    local env=$1
    
    if [ ! -f "config/env.$env" ]; then
        print_error "Environment file config/env.$env not found"
        exit 1
    fi
    
    if [ ! -f "config/apache/$env.conf" ]; then
        print_error "Apache configuration config/apache/$env.conf not found"
        exit 1
    fi
    
    if [ ! -f "config/websocket/$env.conf" ]; then
        print_error "WebSocket configuration config/websocket/$env.conf not found"
        exit 1
    fi
    
    print_success "Environment $env validation passed"
}

# Function to deploy environment
deploy_environment() {
    local env=$1
    
    print_status "Deploying $env environment..."
    
    # Switch to environment
    print_status "Switching to $env environment..."
    ./config/switch-env.sh "$env"
    
    # Generate configurations
    print_status "Generating configurations..."
    ./config/generate-configs.sh
    
    # Stop existing containers
    print_status "Stopping existing Docker containers..."
    docker-compose down
    
    # Build and start containers
    print_status "Building and starting Docker containers..."
    docker-compose up -d --build
    
    # Wait for containers to be ready
    print_status "Waiting for containers to be ready..."
    sleep 10
    
    # Check container status
    print_status "Checking container status..."
    if docker-compose ps | grep -q "Up"; then
        print_success "Containers are running successfully"
    else
        print_error "Some containers failed to start"
        docker-compose logs
        exit 1
    fi
    
    print_success "$env environment deployed successfully!"
}

# Function to show deployment status
show_status() {
    print_status "Current deployment status:"
    echo ""
    
    # Environment info
    if [ -f "config/env" ]; then
        source config/env
        echo "  Environment: $ENV"
        echo "  Domain: $DOMAIN"
        echo "  SSL Certificate: $SSL_CERT_PATH"
        echo "  SSL Key: $SSL_KEY_PATH"
    else
        echo "  No environment file found"
    fi
    
    echo ""
    
    # Docker status
    if command -v docker-compose > /dev/null 2>&1; then
        echo "  Docker containers:"
        docker-compose ps
    else
        echo "  Docker-compose not available"
    fi
    
    echo ""
    
    # Configuration files
    echo "  Configuration files:"
    if [ -f "apache-ssl.conf" ]; then
        echo "    ✅ Apache: apache-ssl.conf"
    else
        echo "    ❌ Apache: apache-ssl.conf (missing)"
    fi
    
    if [ -f "websocket.conf" ]; then
        echo "    ✅ WebSocket: websocket.conf"
    else
        echo "    ❌ WebSocket: websocket.conf (missing)"
    fi
    
    if [ -d "ssl" ] && [ "$(ls -A ssl)" ]; then
        echo "    ✅ SSL certificates: ssl/"
    else
        echo "    ❌ SSL certificates: ssl/ (missing or empty)"
    fi
}

# Function to show help
show_help() {
    echo "🚀 Deployment Script for PHP Docker App"
    echo ""
    echo "Usage: $0 [COMMAND] [ENVIRONMENT]"
    echo ""
    echo "Commands:"
    echo "  deploy [dev|prod]  - Deploy to specified environment"
    echo "  dev                - Deploy to development environment"
    echo "  prod               - Deploy to production environment"
    echo "  status             - Show current deployment status"
    echo "  validate [env]     - Validate environment configuration"
    echo "  help               - Show this help message"
    echo ""
    echo "Examples:"
    echo "  $0 deploy dev      # Deploy to development"
    echo "  $0 deploy prod     # Deploy to production"
    echo "  $0 dev             # Quick deploy to development"
    echo "  $0 prod            # Quick deploy to production"
    echo "  $0 status          # Show current status"
    echo "  $0 validate dev    # Validate dev environment"
    echo ""
    echo "Environment files:"
    echo "  config/env.dev     - Development configuration"
    echo "  config/env.prod    - Production configuration"
    echo ""
    echo "Dependencies:"
    echo "  - Docker running"
    echo "  - docker-compose installed"
    echo "  - SSL certificates configured"
}

# Main script logic
main() {
    # Check if we're in the right directory
    if [ ! -f "docker-compose.yml" ]; then
        print_error "This script must be run from the project root directory"
        print_status "Please run: ./config/deploy.sh"
        exit 1
    fi
    
    # Check dependencies
    check_docker
    check_docker_compose
    
    case "${1:-help}" in
        "deploy")
            if [ -z "$2" ]; then
                print_error "Please specify environment: dev or prod"
                exit 1
            fi
            validate_environment "$2"
            deploy_environment "$2"
            ;;
        "dev")
            validate_environment "dev"
            deploy_environment "dev"
            ;;
        "prod")
            validate_environment "prod"
            deploy_environment "prod"
            ;;
        "status")
            show_status
            ;;
        "validate")
            if [ -z "$2" ]; then
                print_error "Please specify environment: dev or prod"
                exit 1
            fi
            validate_environment "$2"
            print_success "Environment $2 is valid"
            ;;
        "help"|*)
            show_help
            ;;
    esac
}

# Run main function with all arguments
main "$@"
