# Delivery API - Backend

API Laravel 12 para plataforma de delivery multi-tenant.

## Setup

```bash
# Iniciar containers
make up

# Setup inicial (instala deps, migrations, etc)
make setup
```

## Comandos

```bash
make up            # Inicia containers
make down          # Para containers
make logs          # Ver logs
make shell         # Acessa container
make composer      # Executar composer
make artisan       # Executar artisan
make migrate       # Rodar migrations
make test          # Executar testes
```

## Acesso

- **API:** http://delivery.local/api
- **MySQL:** localhost:3306
- **Redis:** localhost:6379

## Arquitetura

Multi-tenant com DB por restaurante (central + tenant DBs).
