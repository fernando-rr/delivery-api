.PHONY: up down restart build logs shell composer artisan migrate test setup clean

up:
	docker compose up -d

down:
	docker compose down

restart:
	docker compose restart

build:
	docker compose build --no-cache

logs:
	docker compose logs -f

shell:
	docker compose exec app bash

composer:
	docker compose exec app composer $(cmd)

artisan:
	docker compose exec app php artisan $(cmd)

migrate:
	docker compose exec app php artisan migrate

test:
	docker compose exec app php artisan test

setup:
	docker compose exec -u root app chown -R delivery:delivery /var/www/html
	docker compose exec app composer install
	@if [ ! -f .env ]; then docker compose exec app cp .env.example .env; fi
	docker compose exec app php artisan key:generate
	docker compose exec app php artisan migrate
	docker compose exec -u root app chmod -R 775 storage bootstrap/cache

fix-permissions:
	docker compose exec -u root app chown -R delivery:delivery /var/www/html/storage /var/www/html/bootstrap/cache
	docker compose exec -u root app chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache
	docker compose exec app php artisan cache:clear
	docker compose exec app php artisan view:clear
	docker compose exec app php artisan config:clear

clean:
	docker compose down -v --rmi all
