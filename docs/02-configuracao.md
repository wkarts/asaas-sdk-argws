# 02 — Configuração

## Configuração direta (sem .env)

```php
<?php

use Asaas\Sdk\AsaasSdk;
use Asaas\Sdk\Config\AsaasConfig;
use Asaas\Sdk\Http\Environment;

$config = new AsaasConfig(
    apiKey: 'SUA_API_KEY',
    environment: Environment::Sandbox,
    appName: 'MinhaApp/1.0',
    timeout: 30.0,
    connectTimeout: 10.0
);

$asaas = new AsaasSdk($config);
```

## Configuração alternativa com .env (opcional)

```php
<?php

use Asaas\Sdk\AsaasSdk;
use Asaas\Sdk\Config\AsaasConfig;
use Asaas\Sdk\Http\Environment;

$config = new AsaasConfig(
    apiKey: getenv('ASAAS_API_KEY') ?: '',
    environment: getenv('ASAAS_ENV') === 'production'
        ? Environment::Production
        : Environment::Sandbox,
    appName: getenv('ASAAS_APP_NAME') ?: 'MinhaApp/1.0'
);

$asaas = new AsaasSdk($config);
```

## User-Agent (obrigatório em contas novas)

O SDK envia o **User-Agent** com o valor de `appName`. Configure sempre:

```php
new AsaasConfig(
    apiKey: 'SUA_API_KEY',
    environment: Environment::Sandbox,
    appName: 'sua-aplicacao/1.0'
);
```

## Timeouts

Ajuste timeout e connectTimeout por necessidade:

```php
new AsaasConfig(
    apiKey: 'SUA_API_KEY',
    environment: Environment::Production,
    appName: 'MinhaApp/1.0',
    timeout: 15.0,
    connectTimeout: 5.0
);
```

## Headers customizados por chamada

Os métodos atuais aceitam `$headers` como array. Exemplo:

```php
$headers = [
    'X-Correlation-Id' => 'req-123',
    'X-Request-Source' => 'checkout'
];

$result = $asaas->payment->listPayments(['limit' => 10], $headers);
```
