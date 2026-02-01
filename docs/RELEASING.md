# Automação de release e publicação no Packagist

Este projeto cria **tag**, **GitHub Release** e **atualiza o Packagist** automaticamente **após o merge/push no branch principal**, desde que o CI passe.

## Requisitos

Configure os secrets no GitHub em **Settings → Secrets and variables → Actions**:

- `PACKAGIST_USERNAME` (seu usuário do Packagist)
- `PACKAGIST_TOKEN` (token do Packagist)

Opcional:

- `PACKAGIST_REPOSITORY` (default: `https://github.com/wkarts/asaas-sdk-argws`)

> Sem `PACKAGIST_USERNAME` e `PACKAGIST_TOKEN`, o workflow **não falha** e apenas registra no log que o update foi ignorado.

## Como o bump automático funciona

O workflow calcula a próxima versão baseado nas mensagens de commit desde a última tag:

- **MAJOR**: se existir `BREAKING CHANGE` ou `!:` em qualquer commit.
- **MINOR**: se existir commit começando com `feat`.
- **PATCH**: caso contrário.

Se não existir tag anterior, a primeira será `v0.1.0`.

## Como forçar major/minor/patch

Use convenções na mensagem de commit:

- **Major**: inclua `BREAKING CHANGE` no corpo do commit ou use `feat!:`/`fix!:` no título.
- **Minor**: use `feat: ...` no início do título do commit.
- **Patch**: qualquer outro padrão (ex.: `fix: ...`, `chore: ...`).

## Como funciona o fluxo

1. Push no branch principal.
2. CI roda a matrix de testes.
3. Se tudo passar:
   - calcula a próxima versão,
   - cria tag `vX.Y.Z`,
   - cria GitHub Release com release notes,
   - atualiza Packagist via API.

## Como desativar/alterar comportamento

Edite o job `release` no arquivo `.github/workflows/ci.yml`. Você pode:

- Ajustar a regra de bump.
- Alterar o repositório do Packagist (`PACKAGIST_REPOSITORY`).
- Remover a etapa de atualização do Packagist.
