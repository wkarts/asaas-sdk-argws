# 14 — Status/Healthcheck/Resiliência

Este guia mostra como verificar saúde da API e aplicar padrões de resiliência **sem alterar o SDK**.

## 1) Healthcheck com chamada leve

Como não há endpoint dedicado de healthcheck no SDK, use uma chamada **leve** já disponível:

- `PaymentService::listPayments(['limit' => 1])`

### Exemplo: função de healthcheck

```php
<?php

use Asaas\Sdk\AsaasSdk;
use Asaas\Sdk\Exception\ApiException;
use Asaas\Sdk\Exception\TransportException;

function asaasHealthCheck(AsaasSdk $sdk): array
{
    $start = microtime(true);

    try {
        $sdk->payment->listPayments(['limit' => 1]);
        $latencyMs = (int) ((microtime(true) - $start) * 1000);

        return [
            'online' => true,
            'status' => 'OK',
            'httpStatus' => 200,
            'errors' => [],
            'latencyMs' => $latencyMs,
        ];
    } catch (ApiException $e) {
        $latencyMs = (int) ((microtime(true) - $start) * 1000);
        $status = $e->getStatusCode();

        if ($status === 401 || $status === 403) {
            return [
                'online' => true,
                'status' => 'AUTH_ERROR',
                'httpStatus' => $status,
                'errors' => $e->getErrors(),
                'latencyMs' => $latencyMs,
            ];
        }

        if ($status === 429) {
            return [
                'online' => true,
                'status' => 'RATE_LIMIT',
                'httpStatus' => $status,
                'errors' => $e->getErrors(),
                'latencyMs' => $latencyMs,
            ];
        }

        return [
            'online' => true,
            'status' => 'SERVER_ERROR',
            'httpStatus' => $status,
            'errors' => $e->getErrors(),
            'latencyMs' => $latencyMs,
        ];
    } catch (TransportException $e) {
        $latencyMs = (int) ((microtime(true) - $start) * 1000);

        return [
            'online' => false,
            'status' => 'TRANSPORT_ERROR',
            'httpStatus' => null,
            'errors' => [],
            'latencyMs' => $latencyMs,
        ];
    }
}
```

## 2) Timeouts específicos para healthcheck

Crie uma instância separada com timeouts menores:

```php
use Asaas\Sdk\AsaasSdk;
use Asaas\Sdk\Config\AsaasConfig;
use Asaas\Sdk\Http\Environment;

$healthSdk = new AsaasSdk(new AsaasConfig(
    apiKey: 'SUA_API_KEY',
    environment: Environment::Production,
    appName: 'MinhaApp/1.0',
    timeout: 3.0,
    connectTimeout: 2.0
));
```

## 3) Retry com backoff (sem libs externas)

```php
use Asaas\Sdk\Exception\ApiException;
use Asaas\Sdk\Exception\TransportException;

function withRetry(callable $fn, int $maxAttempts = 3): mixed
{
    $attempt = 0;

    while (true) {
        $attempt++;
        try {
            return $fn();
        } catch (TransportException $e) {
            if ($attempt >= $maxAttempts) {
                throw $e;
            }
        } catch (ApiException $e) {
            $status = $e->getStatusCode();
            // Retry apenas em 429 ou 5xx
            if (!in_array($status, [429, 500, 502, 503, 504], true) || $attempt >= $maxAttempts) {
                throw $e;
            }
        }

        $base = 200; // ms
        $jitter = random_int(0, 100);
        usleep(($base * $attempt + $jitter) * 1000);
    }
}

$result = withRetry(fn() => $asaas->payment->listPayments(['limit' => 1]));
```

## 4) Circuit Breaker simples

### Implementação em memória

```php
final class CircuitBreaker
{
    private int $failures = 0;
    private int $openedAt = 0;

    public function __construct(
        private int $threshold = 3,
        private int $cooldownSeconds = 30
    ) {}

    public function allowRequest(): bool
    {
        if ($this->failures < $this->threshold) {
            return true;
        }

        if (time() - $this->openedAt > $this->cooldownSeconds) {
            $this->failures = 0; // HALF_OPEN
            return true;
        }

        return false; // OPEN
    }

    public function reportSuccess(): void
    {
        $this->failures = 0;
    }

    public function reportFailure(): void
    {
        $this->failures++;
        if ($this->failures >= $this->threshold) {
            $this->openedAt = time();
        }
    }
}
```

### Exemplo com Laravel Cache

```php
use Illuminate\Support\Facades\Cache;

class CachedCircuitBreaker
{
    public function allow(string $key, int $threshold = 3, int $cooldown = 30): bool
    {
        $state = Cache::get($key, ['failures' => 0, 'openedAt' => 0]);

        if ($state['failures'] < $threshold) {
            return true;
        }

        if (time() - $state['openedAt'] > $cooldown) {
            Cache::put($key, ['failures' => 0, 'openedAt' => 0], $cooldown);
            return true;
        }

        return false;
    }

    public function failure(string $key, int $threshold = 3, int $cooldown = 30): void
    {
        $state = Cache::get($key, ['failures' => 0, 'openedAt' => 0]);
        $state['failures']++;
        if ($state['failures'] >= $threshold) {
            $state['openedAt'] = time();
        }
        Cache::put($key, $state, $cooldown);
    }

    public function success(string $key, int $cooldown = 30): void
    {
        Cache::put($key, ['failures' => 0, 'openedAt' => 0], $cooldown);
    }
}
```

## 5) Observabilidade

- **Correlation ID:** use headers customizados.

```php
$headers = ['X-Correlation-Id' => 'req-123'];
$asaas->payment->listPayments(['limit' => 1], $headers);
```

- **Latência:** meça com `microtime(true)`.
- **Logs seguros:** nunca logue `apiKey`.

## 6) Multi-tenant com healthcheck e cache

```php
$cacheKey = "health:tenant:{$tenantId}";

$result = $cache->get($cacheKey);
if ($result === null) {
    $result = asaasHealthCheck($sdk);
    $cache->set($cacheKey, $result, 60);
}

if ($result['status'] === 'AUTH_ERROR') {
    // Quarentena do tenant: bloqueie operações até troca de chave
}
```
