# 13 — Geração OpenAPI e paridade

A SDK é gerada a partir da documentação pública do Asaas. O repositório inclui scripts para:

- **Baixar OpenAPI**
- **Gerar serviços/DTOs**
- **Verificar paridade da fachada**

## Build do OpenAPI

```bash
composer asaas:build-openapi
```

Opcionalmente, informe URL e output:

```bash
php bin/build-openapi.php "https://docs.asaas.com/reference/comece-por-aqui" resources/openapi.json
```

## Gerar SDK

```bash
composer asaas:generate
```

Isso gera arquivos em:

- `src/Service/Generated`
- `src/Model/Generated`

## Verificar paridade

```bash
composer asaas:verify
```

O script compara a lista de serviços esperados com os expostos em `AsaasSdk`.
