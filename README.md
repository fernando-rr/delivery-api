# Delivery API - Backend

API backend para plataforma de delivery multi-tenant desenvolvida em Laravel 12.

## Objetivo

Este repositório contém a API backend que gerencia:
- Sistema central (SaaS) com restaurantes, planos e assinaturas
- APIs tenant para cada restaurante (produtos, pedidos, clientes)
- Autenticação e roteamento multi-tenant
- Processamento assíncrono de notificações e faturas

## Arquitetura

- **Framework:** Laravel 12 (PHP 8.3+)
- **Autenticação:** Laravel Sanctum
- **Banco de dados:** MySQL 8.0+ (central + múltiplos DBs tenant)
- **Cache/Queue:** Redis 7.0+
- **Storage:** S3/Spaces
- **Padrão:** Multi-tenant com DB por restaurante
- **PHP:** 8.3+
- **Composer:** 2.6+

## Estrutura do Projeto

```
├── app/
│   ├── Http/Controllers/
│   │   ├── Central/          # Controllers do sistema central
│   │   └── Tenant/           # Controllers dos tenants
│   ├── Models/
│   │   ├── Central/          # Models do sistema central
│   │   └── Tenant/           # Models dos tenants
│   ├── Jobs/                 # Jobs para processamento assíncrono
│   ├── Services/             # Services para lógica de negócio
│   └── Middleware/            # Middleware customizado
├── database/
│   ├── migrations/
│   │   ├── central/          # Migrations do sistema central
│   │   └── tenant/           # Migrations dos tenants
│   └── seeders/
├── config/
├── routes/
│   ├── central.php           # Rotas do sistema central
│   └── tenant.php            # Rotas dos tenants
└── tests/
    ├── Feature/              # Testes de integração
    └── Unit/                 # Testes unitários
```

## Configuração Inicial

1. Clone o repositório
2. Execute `composer install`
3. Configure o arquivo `.env`
4. Execute as migrations: `php artisan migrate`
5. Execute `php artisan serve`

## Requisitos Mínimos

- PHP 8.3+
- Composer 2.6+
- MySQL 8.0+
- Redis 7.0+
- Node.js 20+ (para assets)

## Endpoints Principais

### Central API
- `POST /api/central/restaurants` - Criar restaurante
- `GET /api/central/restaurants` - Listar restaurantes
- `GET /api/central/plans` - Listar planos
- `POST /api/central/subscriptions` - Criar assinatura

### Tenant API
- `POST /api/orders` - Criar pedido
- `GET /api/orders` - Listar pedidos
- `GET /api/orders/{id}` - Detalhes do pedido
- `PATCH /api/orders/{id}/status` - Atualizar status
- `GET /api/products` - Listar produtos
- `POST /api/products` - Criar produto
- `GET /api/customers` - Listar clientes
- `POST /api/customers` - Criar cliente

## Desenvolvimento

Este projeto segue os padrões de Clean Code e SOLID, com foco em:
- Separação clara entre lógica central e tenant
- Middleware para identificação de tenant
- Jobs assíncronos para processamento pesado
- Testes automatizados (PHPUnit)
- Documentação da API (OpenAPI/Swagger)
- CI/CD com GitHub Actions
- Docker para desenvolvimento

## Tecnologias Utilizadas

- **Laravel 12** - Framework PHP
- **Laravel Sanctum** - Autenticação API
- **Laravel Horizon** - Monitoramento de filas
- **Laravel Telescope** - Debug e profiling
- **Laravel Pint** - Code style fixer
- **Laravel Pest** - Framework de testes
- **MySQL 8.0+** - Banco de dados
- **Redis 7.0+** - Cache e filas
- **Docker** - Containerização

## Licença

MIT License