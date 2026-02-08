# 11 — Exemplos Laravel (ERP multi-tenant)

Este guia mostra **modelos prontos** de uso da SDK em um ERP Laravel (10.x/11.x),
com multi-tenant, idempotência e organização por camadas.

> A ideia aqui é: **Controller fino**, regra em `Service`, e a SDK apenas como cliente da API.

---

## 1) Instalação

```bash
composer require argws/asaas-sdk-php
```

---

## 2) Config (env) + Service Provider

### 2.1 `.env`

```dotenv
ASAAS_APP_NAME="meu-erp/1.0"
ASAAS_ENV=sandbox
ASAAS_API_KEY=seu_token
```

### 2.2 `config/asaas.php`

```php
<?php

return [
    'app_name' => env('ASAAS_APP_NAME', 'meu-erp'),
    'env'      => env('ASAAS_ENV', 'sandbox'), // sandbox|production
    'api_key'  => env('ASAAS_API_KEY'),
];
```

### 2.3 Provider (single-tenant simples)

`app/Providers/AsaasServiceProvider.php`

```php
<?php

declare(strict_types=1);

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Asaas\Sdk\AsaasSdk;
use Asaas\Sdk\Config\AsaasConfig;
use Asaas\Sdk\Http\Environment;

final class AsaasServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(AsaasSdk::class, function () {
            $env = config('asaas.env') === 'production'
                ? Environment::Production
                : Environment::Sandbox;

            return new AsaasSdk(new AsaasConfig(
                apiKey: (string) config('asaas.api_key'),
                environment: $env,
                appName: (string) config('asaas.app_name'),
                timeout: 30.0,
                connectTimeout: 10.0,
            ));
        });
    }
}
```

Registre o provider em `config/app.php` (se você não usa auto-discovery).

---

## 3) Multi-tenant (por empresa/filial)

Quando cada empresa tem sua própria API Key, você deve resolver a SDK por request.

### 3.1 Exemplo: bind por request (com `TenantResolver`)

```php
$this->app->bind(AsaasSdk::class, function ($app) {
    $tenant = $app->make(\App\Support\TenantResolver::class)->current();

    $env = $tenant->asaas_env === 'production'
        ? Environment::Production
        : Environment::Sandbox;

    return new AsaasSdk(new AsaasConfig(
        apiKey: $tenant->asaas_api_key,
        environment: $env,
        appName: 'meu-erp/1.0',
    ));
});
```

---

## 4) Camada de negócio: `AsaasBillingService`

### 4.1 Estrutura sugerida

```
app/Services/Asaas/
  AsaasBillingService.php
  AsaasCustomerService.php
  AsaasWebhookService.php
```

### 4.2 `AsaasCustomerService` (ensure + persistência)

```php
<?php

declare(strict_types=1);

namespace App\Services\Asaas;

use Asaas\Sdk\AsaasSdk;

final class AsaasCustomerService
{
    public function __construct(private AsaasSdk $asaas) {}

    /**
     * Garante Customer no Asaas para um cliente do ERP.
     * Retorna o ID remoto (cus_...).
     */
    public function ensureCustomer(array $cliente): string
    {
        if (!empty($cliente['asaas_customer_id'])) {
            return (string) $cliente['asaas_customer_id'];
        }

        $cpfCnpj = preg_replace('/\D+/', '', (string) ($cliente['cpf_cnpj'] ?? ''));

        if ($cpfCnpj !== '') {
            $found = $this->asaas->customer->listCustomers(query: [
                'cpfCnpj' => $cpfCnpj,
                'limit' => 1,
            ]);

            if (is_array($found) && !empty($found['data'][0]['id'])) {
                return (string) $found['data'][0]['id'];
            }
        }

        $payload = array_filter([
            'name' => $cliente['nome'] ?? 'Cliente',
            'cpfCnpj' => $cpfCnpj ?: null,
            'email' => $cliente['email'] ?? null,
            'phone' => $cliente['telefone'] ?? null,
        ], static fn($v) => $v !== null && $v !== '');

        $created = $this->asaas->customer->createNewCustomer(payload: $payload);

        return (string) ($created['id'] ?? '');
    }
}
```

### 4.3 `AsaasBillingService` (criar/atualizar cobrança)

A regra de ouro: **se já existe `asaas_payment_id`, não crie novo**.

```php
<?php

declare(strict_types=1);

namespace App\Services\Asaas;

use Asaas\Sdk\AsaasSdk;

final class AsaasBillingService
{
    public function __construct(private AsaasSdk $asaas) {}

    public function createPayment(string $asaasCustomerId, array $fatura, string $billingType = 'BOLETO'): array
    {
        $payload = [
            'customer' => $asaasCustomerId,
            'billingType' => $billingType,
            'value' => (float) ($fatura['valor'] ?? 0),
            'dueDate' => (string) ($fatura['vencimento'] ?? date('Y-m-d')),
            'description' => 'Fatura #' . ($fatura['id'] ?? ''),
        ];

        return $this->asaas->payment->createPayment(payload: $payload);
    }

    public function getPayment(string $paymentId): array
    {
        return $this->asaas->payment->retrieveASinglePayment(pathParams: ['id' => $paymentId]);
    }

    public function updatePayment(string $paymentId, array $changes): array
    {
        return $this->asaas->payment->updateExistingPayment(
            pathParams: ['id' => $paymentId],
            payload: $changes
        );
    }
}
```

---

## 5) Controller fino (exemplo)

```php
<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\Asaas\AsaasCustomerService;
use App\Services\Asaas\AsaasBillingService;

final class AsaasCobrancasController
{
    public function gerar(Request $request, AsaasCustomerService $customers, AsaasBillingService $billing)
    {
        $cliente = [
            'id' => 10,
            'nome' => 'Cliente XPTO',
            'cpf_cnpj' => '00.586.050/0001-00',
            'email' => 'financeiro@cliente.com.br',
            'asaas_customer_id' => null,
        ];

        $fatura = [
            'id' => 287,
            'valor' => 150.00,
            'vencimento' => '2026-02-10',
            'asaas_payment_id' => null,
        ];

        $asaasCustomerId = $customers->ensureCustomer($cliente);

        if (!empty($fatura['asaas_payment_id'])) {
            return $billing->updatePayment($fatura['asaas_payment_id'], [
                'dueDate' => $fatura['vencimento'],
                'value' => $fatura['valor'],
            ]);
        }

        $created = $billing->createPayment($asaasCustomerId, $fatura, 'BOLETO');

        // TODO: persistir $created['id'] em $fatura['asaas_payment_id']
        return $created;
    }
}
```

---

## 6) Webhooks no Laravel (modelo)

### 6.1 Rota

```php
Route::post('/webhooks/asaas', [\App\Http\Controllers\AsaasWebhookController::class, 'handle']);
```

### 6.2 Controller + idempotência

```php
<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

final class AsaasWebhookController
{
    public function handle(Request $request)
    {
        $payload = $request->all();
        $event = $payload['event'] ?? 'UNKNOWN';
        $paymentId = $payload['payment']['id'] ?? $payload['paymentId'] ?? null;

        // dedupe simples (em produção use tabela)
        if ($paymentId) {
            $key = 'asaas:webhook:' . $event . ':' . $paymentId;
            if (Cache::has($key)) {
                return response()->json(['ok' => true, 'deduped' => true]);
            }
            Cache::put($key, true, now()->addMinutes(30));
        }

        // TODO: aplicar regra (ex.: marcar fatura como paga)
        return response()->json(['ok' => true]);
    }
}
```

---

## 7) Onde descobrir os métodos corretos

- Use a referência gerada do código: **[99 — Referência de endpoints](99-reference-endpoints.md)**
- Use o Playground Explorer para testar payloads antes de codar

