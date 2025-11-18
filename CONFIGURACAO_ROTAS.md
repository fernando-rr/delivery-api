# Configuração de Rotas - Delivery API

## Problema Identificado

O erro 500 em `delivery.rinaldi.dev.br` era causado pela configuração do **Traefik**, não pelo Laravel!

### Causa Raiz
O Traefik estava configurado para rotear **apenas** requisições com prefixo `/api`:
```yaml
traefik.http.routers.delivery.rule=Host(`delivery.local`) && PathPrefix(`/api`)
```

Quando acessava `delivery.rinaldi.dev.br` (sem `/api`), o Traefik não encontrava nenhuma regra correspondente e retornava **erro 500**.

## Solução Implementada

### 1. Configuração do Traefik (`docker-compose.yml`)

Foram criadas 4 rotas no Traefik com prioridades diferentes:

#### Rotas API (Prioridade 100 - maior)
- `delivery-api` - HTTP com `/api`
- `delivery-api-secure` - HTTPS com `/api`

#### Rotas Web/Front-end (Prioridade 10 - menor)
- `delivery-web` - HTTP sem `/api`
- `delivery-web-secure` - HTTPS sem `/api`

**Como funciona:**
- Requisições para `/api/*` → vão para o Laravel (API)
- Requisições para `/*` (qualquer outra) → vão para o Laravel (retorna 404 por enquanto)

### 2. Configuração do Laravel (`routes/web.php`)

Foi configurado um `fallback` que retorna 404 para todas as rotas web:

```php
Route::fallback(function () {
    abort(404, 'Rota não encontrada. Use as rotas /api para acessar a API.');
});
```

**Por quê?**
- Por enquanto, apenas as rotas `/api` são utilizadas
- No futuro, o front-end React/Vue será servido aqui
- Evita erros 500 e retorna um 404 apropriado

## Como Aplicar

Para aplicar as mudanças, é necessário recriar o container do nginx:

```bash
docker-compose up -d --force-recreate nginx
```

Ou reiniciar todos os serviços:

```bash
docker-compose down
docker-compose up -d
```

## Resultado Esperado

### Antes
- `delivery.rinaldi.dev.br` → ❌ Erro 500 (Traefik)
- `delivery.rinaldi.dev.br/api` → ✅ API funcionando

### Depois
- `delivery.rinaldi.dev.br` → ✅ HTTP 404 (Laravel)
- `delivery.rinaldi.dev.br/api` → ✅ API funcionando
- `delivery.rinaldi.dev.br/qualquer-coisa` → ✅ HTTP 404 (Laravel)

## Próximos Passos

Quando o front-end estiver pronto:
1. Remover o `fallback` em `routes/web.php`
2. Servir os arquivos estáticos do front-end
3. As rotas `/api` continuarão funcionando normalmente
