# 10 — Multi-tenant

A recomendação é **não usar singleton global**. Crie uma instância por tenant.

## Fábrica simples

```php
<?php

use Asaas\Sdk\AsaasSdk;
use Asaas\Sdk\Config\AsaasConfig;
use Asaas\Sdk\Http\Environment;

final class AsaasSdkFactory
{
    /** @var array<string, AsaasSdk> */
    private array $cache = [];

    public function forTenant(string $tenantId, string $apiKey, bool $prod = false): AsaasSdk
    {
        $key = $tenantId . ':' . ($prod ? 'prod' : 'sandbox');

        if (!isset($this->cache[$key])) {
            $config = new AsaasConfig(
                apiKey: $apiKey,
                environment: $prod ? Environment::Production : Environment::Sandbox,
                appName: 'MinhaApp/1.0'
            );
            $this->cache[$key] = new AsaasSdk($config);
        }

        return $this->cache[$key];
    }
}
```

## Rotação de chaves

```php
$sdk = $factory->forTenant($tenantId, $apiKeyAtual);

// Se a chave foi rotacionada:
$sdk->setApiKey($novaApiKey);
```

## Evitar conflito de ambiente

- Mantenha o ambiente no cache key do tenant.
- Nunca reutilize uma instância de produção para sandbox (ou vice-versa).
