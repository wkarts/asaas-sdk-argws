# SubscriptionService

Acesso via fachada: `$asaas->subscription`.

| Método (SDK) | HTTP | Path |
|---|---|---|
| `listSubscriptions` | GET | `/v3/subscriptions` |
| `createNewSubscription` | POST | `/v3/subscriptions` |
| `createSubscriptionWithCreditCard` | POST | `/v3/subscriptions/` |
| `removeSubscription` | DELETE | `/v3/subscriptions/{id}` |
| `retrieveASingleSubscription` | GET | `/v3/subscriptions/{id}` |
| `updateExistingSubscription` | PUT | `/v3/subscriptions/{id}` |
| `updateSubscriptionCreditCard` | PUT | `/v3/subscriptions/{id}/creditCard` |
| `removeConfigurationForIssuanceOfInvoices` | DELETE | `/v3/subscriptions/{id}/invoiceSettings` |
| `retrieveConfigurationForIssuanceOfInvoices` | GET | `/v3/subscriptions/{id}/invoiceSettings` |
| `createConfigurationForIssuanceOfInvoices` | POST | `/v3/subscriptions/{id}/invoiceSettings` |
| `updateConfigurationForIssuanceOfInvoices` | PUT | `/v3/subscriptions/{id}/invoiceSettings` |
| `listInvoicesForSubscriptionCharges` | GET | `/v3/subscriptions/{id}/invoices` |
| `generateSubscriptionBooklet` | GET | `/v3/subscriptions/{id}/paymentBook` |
| `listPaymentsOfASubscription` | GET | `/v3/subscriptions/{id}/payments` |

## Exemplo rápido

```php
$result = $asaas->subscription->listSubscriptions(
    pathParams: [],
    query: [],
    headers: [],
    payload: null
);
```
