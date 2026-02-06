# Checklist de Auditoria (oficial)

> Objetivo: validar se a SDK PHP está **no mesmo nível de cobertura/qualidade** esperado (base Java + OpenAPI) e se o Playground expõe corretamente **todos** os endpoints.

## A. SDK (código e geração)

- [ ] `resources/openapi.json` existe e está válido (JSON)
- [ ] `composer asaas:sdk-build` executa sem erro
- [ ] `git diff --exit-code` após `asaas:sdk-build` (build determinístico)
- [ ] `composer asaas:verify` (paridade de serviços OK)
- [ ] `composer test` passa
- [ ] `composer lint` passa (PHPStan + CS Fixer --dry-run)

## B. Playground (API Proxy)

- [ ] `GET /openapi.json` retorna OpenAPI com `servers` mostrando URL real
- [ ] Swagger UI lista endpoints (não apenas `/`)
- [ ] Chamadas via proxy aceitam **2 formatos**:
  - [ ] formato padrão (body com `{"args": [...]}`)
  - [ ] formato shorthand (body direto do recurso)

## C. Observabilidade

- [ ] Logs mascaram dados sensíveis (API key/token, cpfCnpj, etc.)
- [ ] `GET /health` retorna `sdk_version`, `env` e valida uma chamada leve

## D. Documentação

- [ ] README descreve uso básico
- [ ] README descreve Playground (Swagger/Scalar)
- [ ] versão da SDK aparece no UI do Playground

## Resultado

- Status: ✅ OK / ⚠️ Parcial / ❌ Falhou
- Evidências (links/logs):
