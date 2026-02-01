# Publicação no Packagist e Releases no GitHub

## Pré-requisitos

- Repositório público no GitHub.
- `composer.json` com `name` e `description` sem termos de oficialidade.
- Tags SemVer (ex.: `v1.0.0`).

## Passo a passo (Packagist)

1. Crie uma conta no Packagist.
2. Clique em **Submit** e informe a URL do repositório no GitHub.
3. Ative o **GitHub Hook** no Packagist para atualizar automaticamente a cada tag.

> O Packagist só publica novas versões quando há **nova tag** no Git.

## Publicando uma versão

```bash
git tag v1.0.0
git push origin v1.0.0
```

Isso dispara:

- **Release automático no GitHub** (via workflow).
- **Atualização automática no Packagist** (via webhook).

## Secrets opcionais (Packagist API)

Se você quiser forçar atualização via API no workflow de release:

- `PACKAGIST_USERNAME`
- `PACKAGIST_TOKEN`
- `PACKAGIST_PACKAGE_URL` (URL do repositório, ex.: `https://github.com/seu-usuario/seu-repo`)

Se esses secrets não existirem, o workflow apenas cria a Release no GitHub.
