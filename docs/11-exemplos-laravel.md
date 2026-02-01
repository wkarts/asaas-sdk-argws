# 11 — Exemplos Laravel

## Binding por request (multi-tenant)

```php
<?php

use Asaas\Sdk\AsaasSdk;
use Asaas\Sdk\Config\AsaasConfig;
use Asaas\Sdk\Http\Environment;
use Illuminate\Support\ServiceProvider;

class AsaasServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(AsaasSdk::class, function ($app) {
            $tenant = $app['tenant']; // resolva via middleware

            return new AsaasSdk(new AsaasConfig(
                apiKey: $tenant->asaas_api_key,
                environment: $tenant->asaas_env === 'production'
                    ? Environment::Production
                    : Environment::Sandbox,
                appName: 'MinhaApp/1.0'
            ));
        });
    }
}
```

## Uso em Controller

```php
use Asaas\Sdk\AsaasSdk;

class PaymentController
{
    public function index(AsaasSdk $asaas)
    {
        return $asaas->payment->listPayments(['limit' => 10]);
    }
}
```

## Tratamento de erros

```php
use Asaas\Sdk\Exception\ApiException;
use Asaas\Sdk\Exception\TransportException;

try {
    $asaas->payment->listPayments(['limit' => 10]);
} catch (ApiException $e) {
    report($e);
} catch (TransportException $e) {
    report($e);
}
```
