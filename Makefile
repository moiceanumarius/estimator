up:
	docker-compose up -d

down:
	docker-compose down

unit-tests:
	docker-compose exec web vendor/bin/phpunit --testdox
