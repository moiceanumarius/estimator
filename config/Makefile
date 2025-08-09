# Configuration Management Makefile

.PHONY: help dev prod setup generate clean status

# Default target
help:
	@echo "🔧 Configuration Management Commands"
	@echo ""
	@echo "Available targets:"
	@echo "  help     - Show this help message"
	@echo "  setup    - Initial SSL setup for both environments"
	@echo "  dev      - Switch to development environment"
	@echo "  prod     - Switch to production environment"
	@echo "  generate - Generate configurations for current environment"
	@echo "  status   - Show current environment status"
	@echo "  clean    - Clean generated configuration files"
	@echo ""
	@echo "Usage examples:"
	@echo "  make dev      # Switch to development"
	@echo "  make prod     # Switch to production"
	@echo "  make setup    # Initial setup"
	@echo "  make status   # Check current environment"

# Initial SSL setup
setup:
	@echo "🔐 Setting up SSL certificates..."
	@./config/setup-ssl.sh

# Switch to development environment
dev:
	@echo "🔄 Switching to development environment..."
	@./config/switch-env.sh dev

# Switch to production environment
prod:
	@echo "🔄 Switching to production environment..."
	@./config/switch-env.sh prod

# Generate configurations for current environment
generate:
	@echo "🔧 Generating configurations..."
	@./config/generate-configs.sh

# Show current environment status
status:
	@echo "📋 Current environment status:"
	@./config/switch-env.sh

# Clean generated configuration files
clean:
	@echo "🧹 Cleaning generated configuration files..."
	@rm -f ../apache-ssl.conf ../websocket.conf
	@echo "✅ Cleaned generated files"

# Install dependencies (if needed)
install-deps:
	@echo "📦 Installing dependencies..."
	@which envsubst > /dev/null || (echo "❌ envsubst not found. Installing gettext..." && brew install gettext)
	@echo "✅ Dependencies installed"

# Full development setup
dev-setup: setup dev generate
	@echo "✅ Development environment setup completed!"

# Full production setup
prod-setup: setup prod generate
	@echo "✅ Production environment setup completed!"
