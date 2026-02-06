# Auditoria oficial (SDK + Playground)

Este diretório padroniza como auditar a SDK **argws/asaas-sdk-php** e o **Playground** (Swagger/Scalar/Explorer), garantindo:

- Paridade de serviços (vs. lista esperada na geração)
- Qualidade de código (PHPStan + CS Fixer)
- Testes (unit + integration quando houver secrets)
- OpenAPI do Asaas (resources/openapi.json) e geração da SDK
- Validação do Playground (OpenAPI dinâmico, rota /openapi.json)

## Como executar localmente

```bash
composer install
composer asaas:sdk-build
composer test
composer lint
composer asaas:verify
```

## Saídas e artefatos

- `resources/openapi.json`: especificação fonte (baixada da doc do Asaas quando forçado)
- `src/Service/Generated/*`: serviços gerados
- `audit/reports/*`: relatórios gerados por auditorias manuais/CI

## Atualizar OpenAPI (quando quiser forçar)

Por padrão o CI **não atualiza** o `resources/openapi.json` para evitar mudanças remotas quebrarem o build.

Para forçar atualização local:

```bash
ASAAS_UPDATE_OPENAPI=1 composer asaas:build-openapi
ASAAS_UPDATE_OPENAPI=1 composer asaas:sdk-build
```

## Fluxo recomendado

1. Abra uma Issue usando o template **Auditoria**.
2. A pipeline `Audit Checklist` roda automaticamente no PR.
3. Se houver gaps (paridade, endpoints faltando etc.), abra tasks no roadmap.

Veja também:
- `audit/checklists/CHECKLIST.md`
- `audit/roadmap/ROADMAP.md`
