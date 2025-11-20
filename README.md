# Delivery Platform - Backend API

## 🎯 Visão Geral

Backend em **Laravel 12 (PHP 8.3)** com arquitetura **Multi-tenant** (Banco de dados por cliente).

## 📐 Arquitetura Multi-Tenant

- **Central DB (`delivery_central`)**: Contém `restaurants`, `plans`, `subscriptions`.
- **Tenant DB (`tenant_{id}`)**: Contém dados isolados (`products`, `orders`, etc).
- **Conexão Dinâmica**: O middleware identifica o tenant e troca a conexão do DB automaticamente.

## 📦 Padrões de Projeto

Seguimos estritamente o fluxo: **Request → Controller → DTO → Service → Model → Database**

### 1. DTOs (Data Transfer Objects)
Validam e tipam os dados de entrada.
- `CreateDTO`: Para inserções (regras `required`).
- `UpdateDTO`: Para atualizações (regras `sometimes`).
- `QueryDTO`: Para filtros de busca.

### 2. Services
Contêm toda a regra de negócio. Um serviço por responsabilidade (SRP).
- `CreatorService`: Criação.
- `UpdaterService`: Atualização.
- `DeleterService`: Remoção.
- `FinderService`: Consultas.

### 3. Controllers
Camada fina que apenas coordena a requisição, chama o DTO e o Service, e retorna JSON padronizado.

## ✅ Checklist para Novos Recursos

Ao criar uma nova entidade (ex: `Product`):
1. **DTOs**: `ProductCreateDTO`, `ProductUpdateDTO`.
2. **Services**: `ProductCreatorService`, `ProductFinderService`, etc.
3. **Controller**: `ProductController`.
4. **Model**: `Product` (com `$fillable` e `casts`).
5. **Rota**: Registrar em `routes/api.php`.
6. **Migration**: Criar tabela no diretório correto (central ou tenant).

## 🚀 Comandos Principais (via Makefile)

```bash
# Setup inicial e shell
make up setup shell

# Migrations
make artisan cmd="migrate --path=database/migrations/central"           # Central
make artisan cmd="migrate --database=tenant --path=database/migrations/tenant" # Tenant

# Outros
make artisan cmd="tinker"
make logs
```

## ⚠️ Regras de Ouro
1. **Sempre use `tenant` connection** para dados de restaurante.
2. **Nunca faça queries cross-tenant** (join entre DBs diferentes).
3. **Jobs** devem receber o contexto do tenant (`tenant_id`).
