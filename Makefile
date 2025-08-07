# Makefile for Estimator App

# Start the application with SSL
up:
	docker-compose up -d

# Stop the application
down:
	docker-compose down

# Generate SSL certificates
ssl:
	./generate-ssl.sh

# Setup SSL and start application
setup-ssl: ssl up

# Run unit tests
unit-tests:
	docker-compose exec web vendor/bin/phpunit --testdox

# View logs
logs:
	docker-compose logs -f web

# Rebuild containers
rebuild:
	docker-compose down
	docker-compose build --no-cache
	docker-compose up -d
