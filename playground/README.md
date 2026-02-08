# Asaas Playground (SDK)

Playground para explorar a superfície pública da SDK, com **Explorer universal**, cenários rápidos, webhooks e logs.

> Este README foca no **uso** do Playground para testar a SDK.
> Para referência detalhada, veja: `docs/16-playground-referencia.md`.

## Rodar local (Docker)

```bash
cd playground
cp .env.example .env
docker compose up -d --build
```

Acesse: http://localhost:8080

## Variáveis principais (.env)

- `ASAAS_API_KEY`: sua API key do Asaas.
- `ASAAS_ENV`: `sandbox` ou `production`.
- `ASAAS_WEBHOOK_TOKEN`: opcional; valida o header `asaas-access-token` nos webhooks.

## Explorer (execução universal)

- Selecione o service e o método.
- Passe parâmetros em JSON (por nome ou posicional).
- Você pode sobrescrever por request:
  - `X-Asaas-Api-Key`
  - `X-Asaas-Env` (`sandbox`/`production`)

## Catálogo

```
GET /sdk/catalog
```

Retorna services/métodos disponíveis por Reflection (útil para automação e geração de docs).

## Webhooks

```
POST /webhooks/asaas
```

Se `ASAAS_WEBHOOK_TOKEN` estiver definido, o header `asaas-access-token` é obrigatório.

## Logs

As execuções ficam registradas (campos sensíveis são removidos).
Use para copiar payloads que funcionaram e depurar erros 400/401.
