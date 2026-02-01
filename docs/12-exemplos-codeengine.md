# 12 — Exemplos CodeEngine

Exemplo em uma aplicação PHP “pura” (CLI ou serviço HTTP próprio).

```php
<?php

use Asaas\Sdk\AsaasSdk;
use Asaas\Sdk\Config\AsaasConfig;
use Asaas\Sdk\Http\Environment;
use Asaas\Sdk\Exception\ApiException;
use Asaas\Sdk\Exception\TransportException;

$apiKey = $argv[1] ?? '';
$env = $argv[2] ?? 'sandbox';

$sdk = new AsaasSdk(new AsaasConfig(
    apiKey: $apiKey,
    environment: $env === 'production' ? Environment::Production : Environment::Sandbox,
    appName: 'CodeEngine/1.0',
    timeout: 20.0,
    connectTimeout: 5.0
));

try {
    $payments = $sdk->payment->listPayments(['limit' => 5]);
    print_r($payments);
} catch (ApiException $e) {
    fwrite(STDERR, "API error: {$e->getStatusCode()}\n");
} catch (TransportException $e) {
    fwrite(STDERR, "Transport error: {$e->getMessage()}\n");
}
```

## Multi-tenant no CodeEngine

```php
class TenantResolver
{
    public function resolveByDomain(string $host): array
    {
        // Retorne apiKey e ambiente com base no domínio
        return ['apiKey' => '...', 'env' => 'sandbox'];
    }
}

$resolver = new TenantResolver();
$tenant = $resolver->resolveByDomain($_SERVER['HTTP_HOST'] ?? '');

$sdk = new AsaasSdk(new AsaasConfig(
    apiKey: $tenant['apiKey'],
    environment: $tenant['env'] === 'production' ? Environment::Production : Environment::Sandbox,
    appName: 'MinhaApp/1.0'
));
```
