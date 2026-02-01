# 03 — Quickstart

## 1) Criar instância

```php
<?php

use Asaas\Sdk\AsaasSdk;
use Asaas\Sdk\Config\AsaasConfig;
use Asaas\Sdk\Http\Environment;

$asaas = new AsaasSdk(new AsaasConfig(
    apiKey: 'SUA_API_KEY',
    environment: Environment::Sandbox,
    appName: 'MinhaApp/1.0'
));
```

## 2) Listar cobranças

```php
$result = $asaas->payment->listPayments(['limit' => 10]);
```

## 3) Criar cobrança

```php
$payload = [
    'customer' => 'cus_123',
    'billingType' => 'BOLETO',
    'value' => 150.00,
    'dueDate' => '2025-01-20'
];

$result = $asaas->payment->createPayment($payload);
```

## 4) Tratamento de erro básico

```php
use Asaas\Sdk\Exception\ApiException;
use Asaas\Sdk\Exception\TransportException;

try {
    $result = $asaas->payment->listPayments(['limit' => 10]);
} catch (ApiException $e) {
    // Erro HTTP da API (4xx/5xx)
    $status = $e->getStatusCode();
    $errors = $e->getErrors();
} catch (TransportException $e) {
    // Timeout, DNS, TLS, rede
}
```

> **Nota:** demais serviços estão presentes no SDK, porém sem métodos gerados no momento.
> Veja `docs/05-servicos-e-endpoints.md` para o inventário completo.
