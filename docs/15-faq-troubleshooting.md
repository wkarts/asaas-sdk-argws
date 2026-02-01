# 15 — FAQ / Troubleshooting

## 1) A SDK não tem métodos para alguns serviços

Isso é esperado no estado atual. Apenas `PaymentService` possui métodos gerados.  
Use os scripts de geração em `docs/13-geracao-openapi-e-paridade.md`.

## 2) Recebo 401/403

- API Key inválida ou do ambiente errado.
- Verifique se está usando Sandbox vs Production corretamente.

## 3) Recebo 429 (rate limit)

- A API está online, mas limitou sua taxa.
- Implemente retry com backoff (ver doc 14).

## 4) Timeout/TransportException

- Verifique conectividade (DNS/TLS).
- Ajuste `timeout`/`connectTimeout` na configuração.

## 5) Packagist não atualiza

- Verifique se as tags foram criadas.
- Confirme secrets `PACKAGIST_USERNAME` e `PACKAGIST_TOKEN`.
