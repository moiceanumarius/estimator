#!/bin/bash

# Rollback Script for PHP Docker App
# Restores previous environment configuration and Docker state

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

# Backup directory
BACKUP_DIR="config/backups"
TIMESTAMP=$(date +"%Y%m%d_%H%M%S")

# Function to create backup
create_backup() {
    local env=$1
    
    print_status "Creating backup of current environment..."
    
    # Create backup directory
    mkdir -p "$BACKUP_DIR"
    
    # Backup current configuration
    if [ -f "config/env" ]; then
        cp "config/env" "$BACKUP_DIR/env.backup.$TIMESTAMP"
        print_success "Environment file backed up"
    fi
    
    if [ -f "apache-ssl.conf" ]; then
        cp "apache-ssl.conf" "$BACKUP_DIR/apache-ssl.conf.backup.$TIMESTAMP"
        print_success "Apache configuration backed up"
    fi
    
    if [ -f "websocket.conf" ]; then
        cp "websocket.conf" "$BACKUP_DIR/websocket.conf.backup.$TIMESTAMP"
        print_success "WebSocket configuration backed up"
    fi
    
    # Backup SSL certificates
    if [ -d "ssl" ]; then
        tar -czf "$BACKUP_DIR/ssl.backup.$TIMESTAMP.tar.gz" ssl/ 2>/dev/null || true
        print_success "SSL certificates backed up"
    fi
    
    print_success "Backup created: $BACKUP_DIR/backup.$TIMESTAMP"
}

# Function to list available backups
list_backups() {
    print_status "Available backups:"
    echo ""
    
    if [ ! -d "$BACKUP_DIR" ] || [ -z "$(ls -A "$BACKUP_DIR" 2>/dev/null)" ]; then
        print_warning "No backups found"
        return
    fi
    
    for backup in "$BACKUP_DIR"/*; do
        if [ -f "$backup" ]; then
            filename=$(basename "$backup")
            size=$(du -h "$backup" | cut -f1)
            echo "  📁 $filename ($size)"
        fi
    done
}

# Function to restore from backup
restore_backup() {
    local backup_file=$1
    
    if [ ! -f "$backup_file" ]; then
        print_error "Backup file not found: $backup_file"
        exit 1
    fi
    
    print_status "Restoring from backup: $backup_file"
    
    # Stop containers
    print_status "Stopping Docker containers..."
    docker-compose down
    
    # Restore based on file type
    if [[ "$backup_file" == *"env.backup"* ]]; then
        cp "$backup_file" "config/env"
        print_success "Environment file restored"
        
        # Regenerate configurations
        print_status "Regenerating configurations..."
        ./config/generate-configs.sh
    elif [[ "$backup_file" == *"apache-ssl.conf.backup"* ]]; then
        cp "$backup_file" "apache-ssl.conf"
        print_success "Apache configuration restored"
    elif [[ "$backup_file" == *"websocket.conf.backup"* ]]; then
        cp "$backup_file" "websocket.conf"
        print_success "WebSocket configuration restored"
    elif [[ "$backup_file" == *"ssl.backup"* ]]; then
        # Remove existing SSL directory
        rm -rf ssl/
        
        # Extract backup
        tar -xzf "$backup_file"
        print_success "SSL certificates restored"
    fi
    
    # Start containers
    print_status "Starting Docker containers..."
    docker-compose up -d
    
    print_success "Rollback completed successfully!"
}

# Function to rollback to previous environment
rollback_environment() {
    local target_env=$1
    
    print_status "Rolling back to $target_env environment..."
    
    # Create backup of current state
    create_backup
    
    # Switch to target environment
    print_status "Switching to $target_env environment..."
    ./config/switch-env.sh "$target_env"
    
    # Regenerate configurations
    print_status "Regenerating configurations..."
    ./config/generate-configs.sh
    
    # Restart containers
    print_status "Restarting Docker containers..."
    docker-compose down
    docker-compose up -d
    
    print_success "Rollback to $target_env completed successfully!"
}

# Function to show help
show_help() {
    echo "🔄 Rollback Script for PHP Docker App"
    echo ""
    echo "Usage: $0 [COMMAND] [OPTIONS]"
    echo ""
    echo "Commands:"
    echo "  backup [env]           - Create backup of current environment"
    echo "  list                   - List available backups"
    echo "  restore <backup_file>  - Restore from specific backup file"
    echo "  rollback [dev|prod]    - Rollback to specified environment"
    echo "  help                   - Show this help message"
    echo ""
    echo "Examples:"
    echo "  $0 backup dev          # Create backup of dev environment"
    echo "  $0 list                # List available backups"
    echo "  $0 restore config/backups/env.backup.20231201_143022"
    echo "  $0 rollback dev        # Rollback to development environment"
    echo "  $0 rollback prod       # Rollback to production environment"
    echo ""
    echo "Backup location: $BACKUP_DIR"
    echo ""
    echo "Note: Always create a backup before making changes!"
}

# Main script logic
main() {
    # Check if we're in the right directory
    if [ ! -f "docker-compose.yml" ]; then
        print_error "This script must be run from the project root directory"
        print_status "Please run: ./config/rollback.sh"
        exit 1
    fi
    
    case "${1:-help}" in
        "backup")
            if [ -z "$2" ]; then
                print_warning "No environment specified, creating backup of current state"
                create_backup "current"
            else
                create_backup "$2"
            fi
            ;;
        "list")
            list_backups
            ;;
        "restore")
            if [ -z "$2" ]; then
                print_error "Please specify backup file to restore from"
                exit 1
            fi
            restore_backup "$2"
            ;;
        "rollback")
            if [ -z "$2" ]; then
                print_error "Please specify environment: dev or prod"
                exit 1
            fi
            rollback_environment "$2"
            ;;
        "help"|*)
            show_help
            ;;
    esac
}

# Run main function with all arguments
main "$@"
