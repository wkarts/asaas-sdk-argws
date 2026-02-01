# 07 — Paginação, filtros e ordenação

A SDK recebe parâmetros de **query** como `array` e repassa para a API.
O utilitário interno `Query::normalize` trata booleans (`true`/`false`).

## Paginação (exemplo com Payments)

```php
$query = [
    'limit' => 10,
    'offset' => 0
];

$result = $asaas->payment->listPayments($query);
```

## Filtros

Passe filtros diretamente no array de query (conforme a documentação oficial do Asaas):

```php
$query = [
    'status' => 'PENDING',
    'customer' => 'cus_123'
];

$result = $asaas->payment->listPayments($query);
```

## Ordenação

Se a API aceitar ordenação por query, você pode usar:

```php
$query = [
    'sort' => 'dueDate',
    'order' => 'asc'
];

$result = $asaas->payment->listPayments($query);
```

> Consulte a documentação oficial do Asaas para a lista completa de filtros e ordenação suportados.
