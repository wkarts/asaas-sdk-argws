# 06 — Exceptions e erros

A SDK expõe as seguintes exceções:

- `Asaas\Sdk\Exception\ApiException`
- `Asaas\Sdk\Exception\TransportException`
- `Asaas\Sdk\Exception\ValidationException`
- `Asaas\Sdk\Exception\PaginationException`

## ApiException

Disparada quando a API retorna status HTTP **>= 400**.

```php
use Asaas\Sdk\Exception\ApiException;

try {
    $asaas->payment->listPayments(query: ['limit' => 10]);
} catch (ApiException $e) {
    $status = $e->getStatusCode();
    $raw = $e->getRawBody();
    $errors = $e->getErrors();
}
```

## TransportException

Disparada por erros de transporte (timeout, DNS, TLS, rede).

```php
use Asaas\Sdk\Exception\TransportException;

try {
    $asaas->payment->listPayments(query: ['limit' => 1]);
} catch (TransportException $e) {
    // Trate indisponibilidade de rede
}
```

## ValidationException

Usada pela `Serializer::validateRequired` quando campos obrigatórios estão ausentes.

```php
use Asaas\Sdk\Util\Serializer;
use Asaas\Sdk\Exception\ValidationException;

try {
    Serializer::validateRequired(['customer', 'value'], ['customer' => null]);
} catch (ValidationException $e) {
    // Campo obrigatório ausente
}
```

## Boas práticas de log

- Nunca logue `apiKey`.
- Registre `statusCode`, `correlation-id` (se usar) e IDs de transação.
- Para `ApiException`, armazene `getErrors()` de forma segura.

## PaginationException

Emitida pelo utilitário opcional `Paginator` quando uma listagem não pode ser concluída com segurança: resposta inválida, página repetida, offset ou total inconsistentes, ou limite de páginas alcançado com registros ainda pendentes. `getOffset()` informa a página que não foi processada. O erro não contém o payload retornado pela API.

Uma exportação deve permanecer temporária até o iterador terminar; não disponibilize um arquivo parcial após essa exceção. Consulte [paginação, filtros e ordenação](07-paginacao-filtros-ordenacao.md).
