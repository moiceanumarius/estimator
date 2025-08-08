# Makefile for Estimator App

# Start the application with SSL
up:
	docker compose up -d

# Stop the application
down:
	docker compose down

# Generate SSL certificates
ssl:
	./generate-ssl.sh

# Setup SSL and start application
setup-ssl: ssl up

# Run unit tests
unit-tests:
	docker compose exec web vendor/bin/phpunit --testdox

# View logs
logs:
	docker compose logs -f web

# Rebuild containers
rebuild:
	docker compose down
	docker compose build --no-cache
	docker compose up -d

# ===== Deployment helpers =====

# Switch environment
env-dev:
	bash config/switch-env.sh dev

env-prod:
	bash config/switch-env.sh prod

# Generate config files from current env
gen-configs:
	bash config/generate-configs.sh

# Apply Apache vhost (development - HTTP only)
apache-apply-dev:
	docker compose exec web bash -lc "\
	  cp /var/www/html/config/apache/dev.conf /etc/apache2/sites-available/localhost.conf && \
	  a2enmod headers rewrite >/dev/null 2>&1 || true && \
	  a2dissite 000-default >/dev/null 2>&1 || true && \
	  a2ensite localhost >/dev/null 2>&1 || true && \
	  apache2ctl configtest && apache2ctl -k graceful"

# Apply Apache vhost (production - HTTPS)
apache-apply-prod:
	docker compose exec web bash -lc "\
	  cp /var/www/html/config/apache/prod.conf /etc/apache2/sites-available/estimator.conf && \
	  a2enmod ssl headers rewrite proxy proxy_wstunnel >/dev/null 2>&1 || true && \
	  a2dissite 000-default >/dev/null 2>&1 || true && \
	  a2ensite estimator >/dev/null 2>&1 || true && \
	  apache2ctl configtest && apache2ctl -k graceful"

# Composer install (production flags)
composer-prod:
	docker compose exec web composer install --no-dev --optimize-autoloader

# WebSocket server controls
ws-start:
	docker compose exec -d web php /var/www/html/websocket-server.php

ws-stop:
	docker compose exec web bash -lc "pkill -f websocket-server.php || true"

# One-shot deploys
deploy-dev: env-dev gen-configs rebuild apache-apply-dev ws-stop ws-start

deploy-prod: env-prod gen-configs rebuild composer-prod apache-apply-prod ws-stop ws-start
