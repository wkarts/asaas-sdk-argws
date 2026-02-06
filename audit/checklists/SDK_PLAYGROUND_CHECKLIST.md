# Checklist de Auditoria — SDK + Playground

> Use este checklist para auditar releases, PRs grandes e mudanças de geração.

## 1) Geração e paridade

- [ ] `composer asaas:sdk-build` executa sem erros
- [ ] `git diff` após `asaas:sdk-build` está limpo (sem arquivos gerados fora do commit esperado)
- [ ] `composer asaas:verify` / `php bin/verify-parity.php` passa
- [ ] Não há serviços esperados faltando no facade (`Asaas\Sdk\AsaasSdk`)

## 2) Qualidade de código

- [ ] `composer lint` passa (PHPStan + CS Fixer dry-run)
- [ ] `composer test` passa
- [ ] Integração (`phpunit --testsuite integration`) passa quando secrets estiverem configuradas

## 3) Playground (Swagger/Scalar/Explorer)

- [ ] `/swagger` carrega e lista endpoints reais (não só "/")
- [ ] `/scalar` carrega
- [ ] `/openapi.json` responde e inclui `servers` com host absoluto + relativo
- [ ] UI exibe a versão instalada da SDK (badge "SDK x.y.z")

## 4) Contratos / compatibilidade

- [ ] Rotas legadas continuam funcionando (`/api/sdk/call/{service}/{method}`)
- [ ] Rotas preferidas funcionam (`/api/sdk/{service}/{method}`)
- [ ] Body "shorthand" funciona (enviar o objeto direto no JSON, sem `args`)

## 5) Segurança

- [ ] Logs do playground mascaram API keys e segredos (não vazam)
- [ ] Headers suportados: `X-Asaas-Api-Key`, `X-Asaas-Key`, `Authorization: Bearer ...`

