# Roadmap (adaptado ao estado atual do repo)

Este roadmap é prático (baseado no que o repositório **já tem hoje**) e foca em:

1) manter paridade com a doc do Asaas
2) melhorar DX (Swagger/Scalar/Explorer)
3) garantir releases previsíveis

## Fase 1 — Auditoria e estabilidade (agora)

- [x] CI com geração, diff, testes, lint e paridade
- [x] Template oficial de auditoria (Issue)
- [x] Workflow dedicado de auditoria com artefato (`audit-checklist`)
- [x] Playground: versão da SDK visível no UI e no OpenAPI
- [x] Playground: OpenAPI exibindo host absoluto (sem ficar só `/`)
- [x] Playground: aceitar body direto como payload (curl simples)

## Fase 2 — Paridade com SDK Java (curto prazo)

- [ ] Mapear, por serviço, recursos/rotas faltantes vs. SDK Java oficial
- [ ] Implementar/gerar métodos ausentes e atualizar o `ParityVerifier`
- [ ] Cobertura de testes por serviço (unit) + alguns cenários de integração

## Fase 3 — DX e documentação (médio prazo)

- [ ] README com exemplos prontos (SDK + Playground)
- [ ] Doc de padrões (payload shorthand, GET args, headers)
- [ ] Postman collection versionada por release

## Fase 4 — Automação de release (médio/longo prazo)

- [ ] Changelog automático (convencional)
- [ ] Tag + GitHub Release + Packagist com pipeline único
- [ ] Publicação do Playground desacoplada de configurações externas (reverse proxy / caddy)
