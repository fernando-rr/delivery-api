# Delivery API - Backend

API backend para plataforma de delivery multi-tenant desenvolvida em Laravel.

## Objetivo

Este repositório contém a API backend que gerencia:
- Sistema central (SaaS) com restaurantes, planos e assinaturas
- APIs tenant para cada restaurante (produtos, pedidos, clientes)
- Autenticação e roteamento multi-tenant
- Processamento assíncrono de notificações e faturas

## Arquitetura

- **Framework:** Laravel 11
- **Autenticação:** Laravel Sanctum
- **Banco de dados:** MySQL (central + múltiplos DBs tenant)
- **Cache/Queue:** Redis
- **Storage:** S3/Spaces
- **Padrão:** Multi-tenant com DB por restaurante

## Estrutura do Projeto

```
├── app/
│   ├── Http/Controllers/
│   │   ├── Central/          # Controllers do sistema central
│   │   └── Tenant/           # Controllers dos tenants
│   ├── Models/
│   │   ├── Central/          # Models do sistema central
│   │   └── Tenant/           # Models dos tenants
│   └── Jobs/                 # Jobs para processamento assíncrono
├── database/
│   ├── migrations/
│   │   ├── central/          # Migrations do sistema central
│   │   └── tenant/           # Migrations dos tenants
│   └── seeders/
├── config/
└── routes/
    ├── central.php           # Rotas do sistema central
    └── tenant.php            # Rotas dos tenants
```

## Configuração Inicial

1. Clone o repositório
2. Execute `composer install`
3. Configure o arquivo `.env`
4. Execute as migrations: `php artisan migrate`
5. Execute `php artisan serve`

## Endpoints Principais

### Central API
- `POST /api/central/restaurants` - Criar restaurante
- `GET /api/central/restaurants` - Listar restaurantes
- `GET /api/central/plans` - Listar planos

### Tenant API
- `POST /api/orders` - Criar pedido
- `GET /api/orders` - Listar pedidos
- `GET /api/products` - Listar produtos
- `POST /api/products` - Criar produto

## Desenvolvimento

Este projeto segue os padrões de Clean Code e SOLID, com foco em:
- Separação clara entre lógica central e tenant
- Middleware para identificação de tenant
- Jobs assíncronos para processamento pesado
- Testes automatizados
- Documentação da API

## Licença

MIT License