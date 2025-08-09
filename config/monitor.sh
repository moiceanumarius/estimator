#!/bin/bash

# Monitoring Script for PHP Docker App
# Monitors application health, Docker containers, and services

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

# Function to check Docker container health
check_docker_health() {
    print_status "Checking Docker container health..."
    echo ""
    
    if ! command -v docker-compose > /dev/null 2>&1; then
        print_error "docker-compose not available"
        return 1
    fi
    
    # Get container status
    local containers=$(docker-compose ps --format "table {{.Name}}\t{{.Status}}\t{{.Ports}}")
    
    if [ -z "$containers" ]; then
        print_warning "No containers found"
        return 1
    fi
    
    echo "$containers" | while IFS= read -r line; do
        if [[ "$line" == *"Up"* ]]; then
            echo "  ✅ $line"
        elif [[ "$line" == *"Exit"* ]]; then
            echo "  ❌ $line"
        else
            echo "  ⚠️  $line"
        fi
    done
    
    echo ""
    
    # Check for unhealthy containers
    local unhealthy=$(docker-compose ps | grep -c "Exit\|unhealthy" || true)
    if [ "$unhealthy" -gt 0 ]; then
        print_warning "Found $unhealthy unhealthy/stopped containers"
        return 1
    else
        print_success "All containers are healthy"
    fi
}

# Function to check service connectivity
check_service_connectivity() {
    print_status "Checking service connectivity..."
    echo ""
    
    # Load environment variables
    if [ -f "config/env" ]; then
        source config/env
    else
        print_error "Environment file not found"
        return 1
    fi
    
    # Check HTTP service
    print_status "Checking HTTP service on port $HTTP_PORT..."
    if curl -s -o /dev/null -w "%{http_code}" "http://$DOMAIN:$HTTP_PORT" 2>/dev/null | grep -q "200\|301\|302"; then
        print_success "HTTP service responding"
    else
        print_warning "HTTP service not responding"
    fi
    
    # Check HTTPS service
    print_status "Checking HTTPS service on port $HTTPS_PORT..."
    if curl -s -o /dev/null -w "%{http_code}" -k "https://$DOMAIN:$HTTPS_PORT" 2>/dev/null | grep -q "200\|301\|302"; then
        print_success "HTTPS service responding"
    else
        print_warning "HTTPS service not responding"
    fi
    
    # Check WebSocket service
    print_status "Checking WebSocket service on port $WEBSOCKET_PORT..."
    if nc -z "$DOMAIN" "$WEBSOCKET_PORT" 2>/dev/null; then
        print_success "WebSocket port open"
    else
        print_warning "WebSocket port not accessible"
    fi
}

# Function to check disk space
check_disk_space() {
    print_status "Checking disk space..."
    echo ""
    
    # Get current directory disk usage
    local current_dir=$(pwd)
    local disk_usage=$(df -h "$current_dir" | tail -1)
    local used_percent=$(echo "$disk_usage" | awk '{print $5}' | sed 's/%//')
    
    echo "  📁 Current directory: $current_dir"
    echo "  💾 Disk usage: $disk_usage"
    
    if [ "$used_percent" -gt 90 ]; then
        print_error "Disk usage is critical: ${used_percent}%"
    elif [ "$used_percent" -gt 80 ]; then
        print_warning "Disk usage is high: ${used_percent}%"
    else
        print_success "Disk usage is normal: ${used_percent}%"
    fi
    
    echo ""
    
    # Check SSL directory size
    if [ -d "ssl" ]; then
        local ssl_size=$(du -sh ssl/ | cut -f1)
        echo "  🔐 SSL directory size: $ssl_size"
    fi
    
    # Check backup directory size
    if [ -d "config/backups" ]; then
        local backup_size=$(du -sh config/backups/ | cut -f1)
        echo "  💾 Backup directory size: $backup_size"
    fi
}

# Function to check log files
check_logs() {
    print_status "Checking log files..."
    echo ""
    
    # Check Docker logs for errors
    print_status "Checking Docker container logs for errors..."
    local error_logs=$(docker-compose logs --tail=50 2>/dev/null | grep -i "error\|exception\|fatal" | tail -5 || true)
    
    if [ -n "$error_logs" ]; then
        print_warning "Found recent errors in logs:"
        echo "$error_logs" | while IFS= read -r line; do
            echo "  ⚠️  $line"
        done
    else
        print_success "No recent errors found in logs"
    fi
    
    echo ""
    
    # Check Apache error logs if accessible
    if [ -f "apache-ssl.conf" ]; then
        print_status "Checking Apache configuration..."
        if apache2ctl -t -f apache-ssl.conf 2>/dev/null; then
            print_success "Apache configuration is valid"
        else
            print_warning "Apache configuration has issues"
        fi
    fi
}

# Function to check SSL certificates
check_ssl_certificates() {
    print_status "Checking SSL certificates..."
    echo ""
    
    if [ -f "config/env" ]; then
        source config/env
    else
        print_error "Environment file not found"
        return 1
    fi
    
    # Check if SSL files exist
    if [ -f "ssl/localhost.crt" ] || [ -f "ssl/estimator.crt" ]; then
        print_success "SSL certificates found"
        
        # Check certificate expiration
        for cert in ssl/*.crt; do
            if [ -f "$cert" ]; then
                local expiry=$(openssl x509 -in "$cert" -noout -enddate 2>/dev/null | cut -d= -f2)
                local subject=$(openssl x509 -in "$cert" -noout -subject 2>/dev/null | sed 's/subject= //')
                
                echo "  🔐 Certificate: $subject"
                echo "  📅 Expires: $expiry"
                
                # Check if certificate is expired
                local expiry_date=$(date -j -f "%b %d %H:%M:%S %Y %Z" "$expiry" +%s 2>/dev/null || echo "0")
                local current_date=$(date +%s)
                
                if [ "$expiry_date" -lt "$current_date" ]; then
                    print_error "Certificate is expired!"
                elif [ $((expiry_date - current_date)) -lt 86400 ]; then
                    print_warning "Certificate expires in less than 24 hours"
                else
                    local days_left=$(((expiry_date - current_date) / 86400))
                    print_success "Certificate valid for $days_left days"
                fi
                echo ""
            fi
        done
    else
        print_warning "No SSL certificates found"
    fi
}

# Function to show system resources
show_system_resources() {
    print_status "System resource usage..."
    echo ""
    
    # Memory usage
    local memory_info=$(free -h | grep "Mem:")
    echo "  💾 Memory: $memory_info"
    
    # CPU load
    local cpu_load=$(uptime | awk -F'load average:' '{print $2}')
    echo "  🚀 CPU Load: $cpu_load"
    
    # Docker resource usage
    if command -v docker > /dev/null 2>&1; then
        local docker_stats=$(docker stats --no-stream --format "table {{.Container}}\t{{.CPUPerc}}\t{{.MemUsage}}" 2>/dev/null | head -5 || true)
        if [ -n "$docker_stats" ]; then
            echo "  🐳 Docker resource usage:"
            echo "$docker_stats" | while IFS= read -r line; do
                echo "    $line"
            done
        fi
    fi
}

# Function to generate health report
generate_health_report() {
    local report_file="health_report_$(date +%Y%m%d_%H%M%S).txt"
    
    print_status "Generating health report: $report_file"
    
    {
        echo "Health Report - $(date)"
        echo "================================"
        echo ""
        
        # Environment info
        if [ -f "config/env" ]; then
            source config/env
            echo "Environment: $ENV"
            echo "Domain: $DOMAIN"
            echo "SSL Certificate: $SSL_CERT_PATH"
            echo ""
        fi
        
        # Docker status
        echo "Docker Status:"
        docker-compose ps
        echo ""
        
        # System resources
        echo "System Resources:"
        free -h
        echo ""
        uptime
        echo ""
        
        # Recent logs
        echo "Recent Logs (last 20 lines):"
        docker-compose logs --tail=20 2>/dev/null || echo "No logs available"
        
    } > "$report_file"
    
    print_success "Health report generated: $report_file"
}

# Function to show help
show_help() {
    echo "📊 Monitoring Script for PHP Docker App"
    echo ""
    echo "Usage: $0 [COMMAND]"
    echo ""
    echo "Commands:"
    echo "  health              - Full health check (default)"
    echo "  docker             - Check Docker container health"
    echo "  services           - Check service connectivity"
    echo "  disk               - Check disk space usage"
    echo "  logs               - Check log files for errors"
    echo "  ssl                - Check SSL certificates"
    echo "  resources          - Show system resource usage"
    echo "  report             - Generate health report"
    echo "  help               - Show this help message"
    echo ""
    echo "Examples:"
    echo "  $0                 # Full health check"
    echo "  $0 docker          # Check Docker health only"
    echo "  $0 ssl             # Check SSL certificates only"
    echo "  $0 report          # Generate health report"
    echo ""
    echo "Note: Some checks require appropriate permissions"
}

# Main script logic
main() {
    # Check if we're in the right directory
    if [ ! -f "docker-compose.yml" ]; then
        print_error "This script must be run from the project root directory"
        print_status "Please run: ./config/monitor.sh"
        exit 1
    fi
    
    case "${1:-health}" in
        "health")
            print_status "Starting comprehensive health check..."
            echo "================================================"
            echo ""
            
            check_docker_health
            echo ""
            check_service_connectivity
            echo ""
            check_disk_space
            echo ""
            check_logs
            echo ""
            check_ssl_certificates
            echo ""
            show_system_resources
            ;;
        "docker")
            check_docker_health
            ;;
        "services")
            check_service_connectivity
            ;;
        "disk")
            check_disk_space
            ;;
        "logs")
            check_logs
            ;;
        "ssl")
            check_ssl_certificates
            ;;
        "resources")
            show_system_resources
            ;;
        "report")
            generate_health_report
            ;;
        "help"|*)
            show_help
            ;;
    esac
}

# Run main function with all arguments
main "$@"
