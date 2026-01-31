# Asaas SDK PHP

SDK PHP oficial para a API do Asaas, com geração automática a partir do OpenAPI das referências e paridade com a SDK Java.

## Instalação

```bash
composer require asaas/sdk-php
```

## Configuração

Use variáveis de ambiente para facilitar:

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

## Geração automática

```bash
composer asaas:build-openapi
composer asaas:generate
composer asaas:verify
```

## Testes e qualidade

```bash
composer test
composer lint
```

## Licença

MIT.
