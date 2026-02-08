# 15 — Exemplos Perfex CRM (módulo)

Abaixo vai um modelo de integração **orientado a produção** para um módulo Perfex CRM (CodeIgniter),
usando esta SDK como cliente HTTP/contrato, sem misturar regra de negócio com chamada remota.

> Objetivo: você pluga isso no seu módulo (ex.: `modules/asaas/`) e mantém a SDK isolada.

---

## 1) Estrutura sugerida (dentro do módulo)

```
modules/asaas/
  libraries/
    AsaasSdkFactory.php
    AsaasGatewayService.php
  services/
    AsaasCustomerSync.php
    AsaasInvoiceBilling.php
  controllers/
    Webhooks.php
```

---

## 2) Factory: cria `AsaasSdk` com env + apiKey

**modules/asaas/libraries/AsaasSdkFactory.php**

```php
<?php

declare(strict_types=1);

use Asaas\Sdk\AsaasSdk;
use Asaas\Sdk\Config\AsaasConfig;
use Asaas\Sdk\Http\Environment;

final class AsaasSdkFactory
{
    public static function make(string $apiKey, string $env, string $appName = 'perfex-asaas'): AsaasSdk
    {
        $environment = ($env === 'production')
            ? Environment::Production
            : Environment::Sandbox;

        return new AsaasSdk(new AsaasConfig(
            apiKey: $apiKey,
            environment: $environment,
            appName: $appName,
            timeout: 30.0,
            connectTimeout: 10.0,
        ));
    }
}
```

---

## 3) Serviço “gateway” do módulo (camada de negócio)

**modules/asaas/libraries/AsaasGatewayService.php**

```php
<?php

declare(strict_types=1);

use Asaas\Sdk\Exception\ApiException;
use Asaas\Sdk\Exception\TransportException;

final class AsaasGatewayService
{
    public function __construct(
        private Asaas\Sdk\AsaasSdk $sdk,
    ) {}

    /**
     * Garante que existe Customer no Asaas para um cliente do Perfex.
     * Retorna o ID remoto (cus_...).
     */
    public function ensureCustomer(array $perfexClient): string
    {
        // 1) Se já tem id remoto salvo, retorna.
        if (!empty($perfexClient['asaas_customer_id'])) {
            return (string) $perfexClient['asaas_customer_id'];
        }

        // 2) Tenta localizar por CPF/CNPJ (quando existir)
        $cpfCnpj = preg_replace('/\D+/', '', (string) ($perfexClient['vat'] ?? ''));

        if ($cpfCnpj !== '') {
            $found = $this->sdk->customer->listCustomers(
                query: ['cpfCnpj' => $cpfCnpj, 'limit' => 1]
            );

            if (is_array($found) && !empty($found['data'][0]['id'])) {
                return (string) $found['data'][0]['id'];
            }
        }

        // 3) Cria no Asaas
        $payload = [
            'name' => (string) ($perfexClient['company'] ?? $perfexClient['firstname'] ?? 'Cliente'),
            'cpfCnpj' => $cpfCnpj ?: null,
            'email' => $perfexClient['email'] ?? null,
            'phone' => $perfexClient['phonenumber'] ?? null,
        ];

        // remove nulls para evitar validações desnecessárias
        $payload = array_filter($payload, static fn($v) => $v !== null && $v !== '');

        try {
            $created = $this->sdk->customer->createNewCustomer(payload: $payload);
        } catch (ApiException|TransportException $e) {
            // aqui você converte para o log/erro padrão do Perfex
            log_activity('Asaas: falha ao criar cliente: ' . $e->getMessage());
            throw $e;
        }

        return (string) ($created['id'] ?? '');
    }

    /**
     * Cria cobrança de uma fatura do Perfex (ex.: boleto/pix).
     */
    public function createPaymentFromInvoice(string $asaasCustomerId, array $invoice, string $billingType = 'BOLETO'): array
    {
        $payload = [
            'customer' => $asaasCustomerId,
            'billingType' => $billingType,
            'value' => (float) ($invoice['total'] ?? 0),
            'dueDate' => date('Y-m-d', strtotime((string) ($invoice['duedate'] ?? 'now'))),
            'description' => 'Fatura #' . ($invoice['id'] ?? ''),
            // 'externalReference' => 'perfex:' . ($invoice['id'] ?? ''), // se você usar
        ];

        return $this->sdk->payment->createPayment(payload: $payload);
    }
}
```

---

## 4) Controller de Webhook do módulo

**modules/asaas/controllers/Webhooks.php** (exemplo)

```php
<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Webhooks extends App_Controller
{
    public function asaas()
    {
        $payload = json_decode(file_get_contents('php://input'), true) ?: [];

        // Se você usa token no header:
        $token = $this->input->get_request_header('asaas-access-token', true);
        $expected = get_option('asaas_webhook_token'); // ou config do módulo

        if ($expected && $token !== $expected) {
            show_error('Webhook token inválido', 401);
        }

        // Ex.: PAYMENT_RECEIVED / PAYMENT_OVERDUE / etc.
        $event = $payload['event'] ?? null;

        // Aqui você direciona para seu service/regras
        log_activity('Asaas webhook recebido: ' . $event);

        // Resposta 200 sempre que processar
        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode(['ok' => true]));
    }
}
```

---

## 5) O que você deve persistir no Perfex

Para não “criar novo” toda vez (o problema clássico de duplicar fatura/pagamento):

- `asaas_customer_id` no cliente
- `asaas_payment_id` (cobrança) na fatura
- `asaas_subscription_id` (assinatura) quando for recorrência
- `asaas_webhook_last_event_at` (opcional) para idempotência

---

## 6) Idempotência (o segredo para produção)

Regra simples:

1. Se existe `asaas_payment_id` na fatura, **atualize/consulte** ao invés de criar de novo.
2. No webhook, processe com **dedupe** (por `payment.id + event`), ou ao menos por timestamp.

Isso evita “criar novo pagamento” quando você só queria alterar uma cobrança existente.
