# Configuração Docker - Delivery API

Este projeto está configurado para funcionar com Traefik + Portainer tanto localmente quanto em produção.

## Requisitos

- Docker
- Docker Compose
- Traefik rodando (pode ser via Portainer)

## Configuração Local

1. Configure o arquivo `.env`:
```bash
TRAEFIK_HOST=delivery.local
APP_URL=http://delivery.local/api
```

2. Adicione ao seu `/etc/hosts` (Linux/Mac) ou `C:\Windows\System32\drivers\etc\hosts` (Windows):
```
127.0.0.1 delivery.local
```

3. Inicie os containers:
```bash
docker-compose up -d
```

4. Execute as migrações:
```bash
docker-compose exec app php artisan migrate
```

A API estará disponível em: `http://delivery.local/api`

## Configuração Produção

1. Configure o arquivo `.env`:
```bash
TRAEFIK_HOST=delivery.rinaldi.dev.br
APP_URL=https://delivery.rinaldi.dev.br/api
APP_ENV=production
APP_DEBUG=false
ACME_EMAIL=seu-email@example.com
```

2. No Portainer, certifique-se de que:
   - O Traefik está rodando na mesma rede Docker
   - A rede `delivery_network` está criada ou será criada automaticamente
   - O certificado Let's Encrypt está configurado no Traefik

3. Deploy via Portainer ou:
```bash
docker-compose up -d
```

A API estará disponível em: `https://delivery.rinaldi.dev.br/api`

## Estrutura

- `Dockerfile`: Imagem PHP-FPM para Laravel
- `docker-compose.yml`: Configuração dos serviços
- `docker/nginx/default.conf`: Configuração do Nginx
- Rotas da API em `/api` prefix

## Comandos Úteis

```bash
# Ver logs
docker-compose logs -f app

# Executar comandos Artisan
docker-compose exec app php artisan [comando]

# Rebuild da imagem
docker-compose build app

# Parar todos os serviços
docker-compose down
```
