.PHONY: up down restart logs shell install setup test lint format artisan

# Docker containers
up:
	docker compose up -d

down:
	docker compose down

restart:
	docker compose restart

logs:
	docker compose logs -f

# Application shell
shell:
	docker compose exec app bash

# Installation and Setup
install:
	docker compose build
	docker compose up -d
	docker compose exec app composer install
	docker compose exec app php artisan key:generate
	docker compose exec app php artisan storage:link

setup:
	docker compose exec app php artisan migrate:fresh --seed
	docker compose exec app php artisan passport:install

# Quality Assurance
test:
	docker compose exec app php artisan test

lint:
	docker compose exec app ./vendor/bin/pint --test
	docker compose exec app ./vendor/bin/phpcs

format:
	docker compose exec -u root -e TMPDIR=/var/www/html/storage/framework/cache app ./vendor/bin/pint
	-docker compose exec -u root -e TMPDIR=/var/www/html/storage/framework/cache app ./vendor/bin/phpcbf

# Helper for artisan commands
# Usage: make artisan cmd="migrate:status"
artisan:
	docker compose exec app php artisan $(cmd)

