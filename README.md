# Asaas SDK PHP

> **NÃO OFICIAL** — Não afiliada ao Asaas.  
> Gerada a partir da documentação pública/OpenAPI e inspirada na SDK Java oficial.  
> Asaas é marca de seus respectivos proprietários.

SDK PHP não oficial para a API do Asaas, com geração automática a partir do OpenAPI das referências e paridade com a SDK Java.

## Instalação

```bash
composer require argws/asaas-sdk-php
```

## Configuração

Use variáveis de ambiente para facilitar (veja também `.env.example`):

```bash
export ASAAS_API_KEY="seu_token"
export ASAAS_ENV="sandbox" # ou production
export ASAAS_APP_NAME="MinhaApp/1.0"
```

```php
<?php

use Asaas\Sdk\AsaasSdk;
use Asaas\Sdk\Config\AsaasConfig;
use Asaas\Sdk\Http\Environment;

$config = new AsaasConfig(
    apiKey: getenv('ASAAS_API_KEY'),
    environment: getenv('ASAAS_ENV') === 'production' ? Environment::Production : Environment::Sandbox,
    appName: getenv('ASAAS_APP_NAME') ?: 'MinhaApp/1.0'
);

$asaas = new AsaasSdk($config);
```

## Paridade e geração automática

Este SDK é gerado a partir da documentação/OpenAPI e busca paridade com a SDK Java oficial:

```bash
composer asaas:build-openapi
composer asaas:generate
composer asaas:verify
```

## Exemplos

### Listar cobranças

```php
$result = $asaas->payment->listPayments(['limit' => 10]);
```

### Criar cliente

```php
$payload = [
    'name' => 'Maria Silva',
    'cpfCnpj' => '12345678901',
    'email' => 'maria@exemplo.com'
];

$result = $asaas->customer->createCustomer($payload);
```

### Criar cobrança

```php
$payload = [
    'customer' => 'cus_123',
    'billingType' => 'BOLETO',
    'value' => 150.00,
    'dueDate' => '2025-01-20'
];

$result = $asaas->payment->createPayment($payload);
```

### Upload de documento (multipart)

```php
$payload = [
    'type' => 'IDENTIFICATION',
    'file' => fopen('/caminho/documento.pdf', 'r')
];

$result = $asaas->accountDocument->uploadAccountDocument($payload);
```

### Sandbox vs Production

```php
$asaas->setEnvironment(Environment::Sandbox);
$asaas->setEnvironment(Environment::Production);
```

## Testes e qualidade

```bash
composer test
composer lint
```

## Variáveis de ambiente

Para testes locais (sandbox):

```bash
ASAAS_API_KEY="seu_token_sandbox"
ASAAS_ENV="sandbox"
ASAAS_APP_NAME="MinhaApp/1.0"
```

## GitHub Secrets (CI)

Configure os secrets no repositório:

- `ASAAS_API_KEY` (sandbox recomendado)
- `ASAAS_ENV` (`sandbox` ou `production`)
- `ASAAS_APP_NAME` (ex.: `argws-asaas-sdk-php/1.0`)

## Publicação (Packagist + GitHub Releases)

Consulte o passo a passo em [`docs/PUBLISHING.md`](docs/PUBLISHING.md). O fluxo esperado é:

1. Commit e tag (ex.: `v1.0.0`)
2. Push da tag para o GitHub
3. Release automática no GitHub e atualização no Packagist via webhook

## Aviso legal

Leia [`DISCLAIMER.md`](DISCLAIMER.md).

## Licença

MIT.
