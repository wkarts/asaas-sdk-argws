# Asaas Playground

Playground completo para explorar 100% da superfície pública da SDK Asaas, com catálogo dinâmico, Explorer universal, cenários rápidos, webhooks e logs.

## Rodar local (Docker)

```bash
cd playground
cp .env.example .env

docker compose up -d --build
```

Acesse: http://localhost:8080

## Configurar .env

Principais variáveis:

- `ASAAS_API_KEY`: token da API Asaas.
- `ASAAS_ENV`: `sandbox` ou `production`.
- `ASAAS_WEBHOOK_TOKEN`: opcional; valida o header `asaas-access-token` nos webhooks.

## Explorer (Execução universal)

- Selecione a classe e o método.
- Passe parâmetros em JSON:
  - Array (`[]`) para argumentos posicionais.
  - Objeto (`{}`) para casar por nome.
- Objetos tipados são hidratados automaticamente via Reflection.

## Catálogo da SDK

Endpoint JSON:

```
GET /sdk/catalog
```

Ele retorna classes, services e assinaturas de métodos geradas por Reflection.

## Webhooks

Endpoint de recepção:

```
POST /webhooks/asaas
```

Se `ASAAS_WEBHOOK_TOKEN` estiver definido, o header `asaas-access-token` é obrigatório.

## Logs

Todas as chamadas de Explorer, Scenarios e Raw ficam registradas em SQLite. Acesse `/logs` para filtrar por ação/sucesso.

## Deploy VPS (Docker Hub + Nginx)

### Docker Compose (pull)

```bash
cd playground
cp .env.example .env

echo "$DOCKERHUB_TOKEN" | docker login -u "$DOCKERHUB_USERNAME" --password-stdin

docker compose -f docker-compose.vps.yml pull

docker compose -f docker-compose.vps.yml up -d
```

### Nginx (HTTP)

```nginx
server {
  listen 80;
  server_name playground-asaas-sdk.argws.com.br;

  client_max_body_size 20m;

  location / {
    proxy_pass http://127.0.0.1:8080;
    proxy_http_version 1.1;
    proxy_set_header Host $host;
    proxy_set_header X-Real-IP $remote_addr;
    proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
    proxy_set_header X-Forwarded-Proto $scheme;
  }
}
```

### TLS com Let's Encrypt

```bash
certbot --nginx -d playground-asaas-sdk.argws.com.br
```

## Docker Hub

A imagem publicada pelo workflow é:

```
${DOCKERHUB_USERNAME}/asaas-sdk-argws-playground
```

Tags:

- `latest`
- `sha-<commit>`

## Codespaces (Try online temporário)

1. Abra um Codespace no repositório.
2. Instale dependências (`composer install`) dentro de `/playground`.
3. Exponha a porta `8080` e publique como **Public**.

Isso serve apenas como demo temporária (não é hosting permanente).
