# Guia de Setup Docker - Delivery API

Este guia explica como configurar e executar a API de Delivery usando Docker, Traefik e Portainer, tanto localmente quanto em produção.

## 📋 Pré-requisitos

### Local
- Docker (v20.10+)
- Docker Compose (v2.0+)
- Traefik configurado e rodando
- Network do Traefik criada: `docker network create traefik-network`

### Produção
- Servidor com Docker e Docker Compose
- Traefik + Portainer configurados
- Domínio apontando para o servidor (delivery.rinaldi.dev.br)

## 🚀 Setup Local

### 1. Criar network do Traefik (se ainda não existir)

```bash
docker network create traefik-network
```

### 2. Configurar arquivo hosts

Adicione ao seu `/etc/hosts`:

```
127.0.0.1 delivery.local
```

### 3. Configurar variáveis de ambiente

```bash
cp .env.example .env
```

Ajuste as variáveis conforme necessário. As principais para ambiente local:

```env
APP_ENV=local
APP_DEBUG=true
APP_URL=http://delivery.local

DB_DATABASE=delivery
DB_USERNAME=delivery
DB_PASSWORD=secret

REDIS_PASSWORD=null
```

### 4. Gerar chave da aplicação

```bash
docker-compose run --rm app php artisan key:generate
```

### 5. Build e start dos containers

```bash
docker-compose up -d --build
```

### 6. Rodar migrations

```bash
docker-compose exec app php artisan migrate
```

### 7. Acessar a aplicação

A API estará disponível em: `http://delivery.local/api`

## 🌐 Setup Produção

### 1. Preparar variáveis de ambiente

Crie um arquivo `.env` no servidor baseado no `.env.production.example`:

```bash
cp .env.production.example .env
```

**IMPORTANTE:** Gere senhas fortes para:
- `APP_KEY` - Use: `php artisan key:generate --show`
- `DB_PASSWORD`
- `DB_ROOT_PASSWORD`
- `REDIS_PASSWORD`

### 2. Build da imagem Docker

Opção A - Build local e push para registry:

```bash
# Build da imagem
docker build -t ghcr.io/seu-usuario/delivery-api:latest .

# Push para registry
docker push ghcr.io/seu-usuario/delivery-api:latest
```

Opção B - Build direto no servidor:

```bash
docker-compose -f docker-compose.prod.yml build
```

### 3. Deploy com Docker Compose

```bash
docker-compose -f docker-compose.prod.yml up -d
```

### 4. Rodar migrations

```bash
docker-compose -f docker-compose.prod.yml exec app php artisan migrate --force
```

### 5. Otimizações de produção

```bash
docker-compose -f docker-compose.prod.yml exec app php artisan config:cache
docker-compose -f docker-compose.prod.yml exec app php artisan route:cache
docker-compose -f docker-compose.prod.yml exec app php artisan view:cache
```

### 6. Acessar a aplicação

A API estará disponível em: `https://delivery.rinaldi.dev.br/api`

## 🔧 Comandos Úteis

### Ver logs

```bash
# Local
docker-compose logs -f app

# Produção
docker-compose -f docker-compose.prod.yml logs -f app
```

### Executar comandos artisan

```bash
# Local
docker-compose exec app php artisan [comando]

# Produção
docker-compose -f docker-compose.prod.yml exec app php artisan [comando]
```

### Acessar shell do container

```bash
# Local
docker-compose exec app bash

# Produção
docker-compose -f docker-compose.prod.yml exec app bash
```

### Restart dos serviços

```bash
# Local
docker-compose restart

# Produção
docker-compose -f docker-compose.prod.yml restart
```

### Limpar cache

```bash
# Local
docker-compose exec app php artisan cache:clear
docker-compose exec app php artisan config:clear
docker-compose exec app php artisan route:clear
docker-compose exec app php artisan view:clear

# Produção
docker-compose -f docker-compose.prod.yml exec app php artisan cache:clear
docker-compose -f docker-compose.prod.yml exec app php artisan config:clear
docker-compose -f docker-compose.prod.yml exec app php artisan route:clear
docker-compose -f docker-compose.prod.yml exec app php artisan view:clear
```

## 🔐 Configuração do Traefik

### Labels importantes no docker-compose.yml (Local)

```yaml
labels:
  - "traefik.enable=true"
  - "traefik.http.routers.delivery-api-local.rule=Host(`delivery.local`) && PathPrefix(`/api`)"
  - "traefik.http.routers.delivery-api-local.entrypoints=web"
  - "traefik.http.services.delivery-api-local.loadbalancer.server.port=80"
```

### Labels importantes no docker-compose.prod.yml (Produção)

```yaml
labels:
  - "traefik.enable=true"
  - "traefik.http.routers.delivery-api.rule=Host(`delivery.rinaldi.dev.br`) && PathPrefix(`/api`)"
  - "traefik.http.routers.delivery-api.entrypoints=websecure"
  - "traefik.http.routers.delivery-api.tls=true"
  - "traefik.http.routers.delivery-api.tls.certresolver=letsencrypt"
```

## 📦 Estrutura de Serviços

### App (Laravel)
- Nginx + PHP-FPM
- Supervisor para gerenciar processos
- Queue worker
- Schedule runner

### MySQL
- Banco de dados principal
- Porta exposta localmente para debug: 3306

### Redis
- Cache
- Sessions
- Queue

## 🐛 Troubleshooting

### Erro de permissões

```bash
docker-compose exec app chown -R laravel:laravel /var/www/html/storage
docker-compose exec app chmod -R 755 /var/www/html/storage
```

### Container não inicia

Verifique os logs:
```bash
docker-compose logs app
```

### Traefik não roteia

1. Verifique se a network existe: `docker network ls | grep traefik`
2. Verifique se o Traefik está rodando
3. Verifique os logs do Traefik: `docker logs traefik`

### Erro de conexão com banco

1. Verifique se o MySQL está rodando: `docker-compose ps mysql`
2. Teste a conexão: `docker-compose exec app php artisan migrate:status`

## 🔄 Deploy Contínuo (CI/CD)

Para configurar CI/CD com GitHub Actions, crie `.github/workflows/deploy.yml`:

```yaml
name: Deploy to Production

on:
  push:
    branches: [main]

jobs:
  deploy:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v3
      
      - name: Build and Push Docker Image
        run: |
          echo ${{ secrets.GITHUB_TOKEN }} | docker login ghcr.io -u ${{ github.actor }} --password-stdin
          docker build -t ghcr.io/${{ github.repository }}:latest .
          docker push ghcr.io/${{ github.repository }}:latest
      
      - name: Deploy to Server
        uses: appleboy/ssh-action@master
        with:
          host: ${{ secrets.SERVER_HOST }}
          username: ${{ secrets.SERVER_USER }}
          key: ${{ secrets.SERVER_SSH_KEY }}
          script: |
            cd /path/to/project
            docker-compose -f docker-compose.prod.yml pull
            docker-compose -f docker-compose.prod.yml up -d
            docker-compose -f docker-compose.prod.yml exec -T app php artisan migrate --force
            docker-compose -f docker-compose.prod.yml exec -T app php artisan config:cache
```

## 📝 Notas Importantes

1. **Segurança**: Nunca commite o arquivo `.env` com credenciais reais
2. **Backups**: Configure backups automáticos do MySQL
3. **Monitoramento**: Configure logs e alertas para produção
4. **SSL**: O Traefik gerencia automaticamente os certificados Let's Encrypt
5. **Escalabilidade**: Para escalar, aumente o número de workers no Supervisor

## 🆘 Suporte

Para problemas ou dúvidas:
1. Verifique os logs dos containers
2. Consulte a documentação do Laravel
3. Verifique a configuração do Traefik
