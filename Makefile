.PHONY: help build up down restart logs shell artisan migrate test clean install

# Cores para output
BLUE := \033[0;34m
GREEN := \033[0;32m
RED := \033[0;31m
NC := \033[0m # No Color

help: ## Mostra esta mensagem de ajuda
	@echo "$(BLUE)Delivery API - Comandos Docker$(NC)"
	@echo ""
	@grep -E '^[a-zA-Z_-]+:.*?## .*$$' $(MAKEFILE_LIST) | sort | awk 'BEGIN {FS = ":.*?## "}; {printf "$(GREEN)%-20s$(NC) %s\n", $$1, $$2}'

# ===== Comandos Locais =====

build: ## Build dos containers locais
	@echo "$(BLUE)Building containers...$(NC)"
	docker-compose build

up: ## Inicia os containers locais
	@echo "$(BLUE)Starting containers...$(NC)"
	docker-compose up -d
	@echo "$(GREEN)Containers iniciados!$(NC)"
	@echo "API disponível em: http://delivery.local/api"

down: ## Para os containers locais
	@echo "$(RED)Stopping containers...$(NC)"
	docker-compose down

restart: ## Reinicia os containers locais
	@echo "$(BLUE)Restarting containers...$(NC)"
	docker-compose restart

logs: ## Mostra os logs dos containers
	docker-compose logs -f app

shell: ## Acessa o shell do container app
	docker-compose exec app bash

artisan: ## Executa um comando artisan (use: make artisan CMD="migrate")
	docker-compose exec app php artisan $(CMD)

migrate: ## Roda as migrations
	@echo "$(BLUE)Running migrations...$(NC)"
	docker-compose exec app php artisan migrate

migrate-fresh: ## Reseta o banco e roda as migrations
	@echo "$(RED)Dropping all tables and migrating...$(NC)"
	docker-compose exec app php artisan migrate:fresh

seed: ## Roda os seeders
	@echo "$(BLUE)Running seeders...$(NC)"
	docker-compose exec app php artisan db:seed

test: ## Roda os testes
	@echo "$(BLUE)Running tests...$(NC)"
	docker-compose exec app php artisan test

cache-clear: ## Limpa todos os caches
	@echo "$(BLUE)Clearing caches...$(NC)"
	docker-compose exec app php artisan cache:clear
	docker-compose exec app php artisan config:clear
	docker-compose exec app php artisan route:clear
	docker-compose exec app php artisan view:clear

optimize: ## Otimiza a aplicação (cache)
	@echo "$(BLUE)Optimizing application...$(NC)"
	docker-compose exec app php artisan config:cache
	docker-compose exec app php artisan route:cache
	docker-compose exec app php artisan view:cache

install: ## Instalação inicial local
	@echo "$(BLUE)Installing application...$(NC)"
	@if [ ! -f .env ]; then cp .env.example .env; fi
	docker-compose up -d --build
	@echo "Waiting for MySQL to be ready..."
	@sleep 10
	docker-compose exec app composer install
	docker-compose exec app php artisan key:generate
	docker-compose exec app php artisan migrate
	@echo "$(GREEN)Instalação concluída!$(NC)"
	@echo "API disponível em: http://delivery.local/api"

clean: ## Remove containers, volumes e imagens
	@echo "$(RED)Cleaning up...$(NC)"
	docker-compose down -v --rmi local

# ===== Comandos de Produção =====

prod-build: ## Build da imagem para produção
	@echo "$(BLUE)Building production image...$(NC)"
	docker build -t delivery-api:latest .

prod-up: ## Inicia containers em produção
	@echo "$(BLUE)Starting production containers...$(NC)"
	docker-compose -f docker-compose.prod.yml up -d

prod-down: ## Para containers em produção
	@echo "$(RED)Stopping production containers...$(NC)"
	docker-compose -f docker-compose.prod.yml down

prod-logs: ## Mostra logs de produção
	docker-compose -f docker-compose.prod.yml logs -f app

prod-shell: ## Acessa shell em produção
	docker-compose -f docker-compose.prod.yml exec app bash

prod-migrate: ## Roda migrations em produção
	@echo "$(BLUE)Running production migrations...$(NC)"
	docker-compose -f docker-compose.prod.yml exec app php artisan migrate --force

prod-optimize: ## Otimiza aplicação em produção
	@echo "$(BLUE)Optimizing production...$(NC)"
	docker-compose -f docker-compose.prod.yml exec app php artisan config:cache
	docker-compose -f docker-compose.prod.yml exec app php artisan route:cache
	docker-compose -f docker-compose.prod.yml exec app php artisan view:cache
	docker-compose -f docker-compose.prod.yml exec app php artisan optimize

prod-deploy: prod-build ## Deploy completo em produção
	@echo "$(BLUE)Deploying to production...$(NC)"
	docker-compose -f docker-compose.prod.yml up -d --build
	docker-compose -f docker-compose.prod.yml exec app php artisan migrate --force
	$(MAKE) prod-optimize
	@echo "$(GREEN)Deploy concluído!$(NC)"

# ===== Utilitários =====

db-backup: ## Backup do banco de dados
	@echo "$(BLUE)Creating database backup...$(NC)"
	docker-compose exec mysql mysqldump -u root -p${DB_ROOT_PASSWORD} ${DB_DATABASE} > backup_$(shell date +%Y%m%d_%H%M%S).sql
	@echo "$(GREEN)Backup criado!$(NC)"

composer-install: ## Instala dependências do composer
	docker-compose exec app composer install

composer-update: ## Atualiza dependências do composer
	docker-compose exec app composer update

composer-require: ## Instala um pacote (use: make composer-require PKG="package/name")
	docker-compose exec app composer require $(PKG)

queue-work: ## Inicia queue worker manualmente
	docker-compose exec app php artisan queue:work

ps: ## Lista containers em execução
	docker-compose ps

stats: ## Mostra estatísticas dos containers
	docker stats $(shell docker-compose ps -q)
