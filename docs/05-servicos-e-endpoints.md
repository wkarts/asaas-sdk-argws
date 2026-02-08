# 05 — Serviços e endpoints (visão cirúrgica)

A fachada `AsaasSdk` expõe os serviços como propriedades públicas (ex.: `$asaas->customer`, `$asaas->payment`, ...).

A SDK foi gerada do OpenAPI do Asaas: cada serviço em `src/Service/*Service.php` **estende** uma classe gerada em `src/Service/Generated/*Service.php`, onde ficam os métodos que chamam `$this->request(...)`.

## Padrão de assinatura (em praticamente todos os métodos gerados)

> Isso é intencional: o gerador mantém uma assinatura única para facilitar automação, logs e a UI do Playground.

```php
public function algumaOperacao(
    array $pathParams = [],  // substitui {param} no path
    array $query = [],       // filtros/paginação/ordenação
    array $headers = [],     // headers extras (além de Authorization)
    ?array $payload = null   // body JSON (POST/PUT/PATCH); use null em GET/DELETE
): mixed
```

### Como montar `pathParams`

Se o endpoint tiver `/v3/customers/{id}`, você passa:

```php
$result = $asaas->customer->retrieveASingleCustomer(
    pathParams: ['id' => 'cus_123'],
);
```

### Como montar `query`

Paginação e filtros:

```php
$result = $asaas->payment->listPayments(
    query: ['limit' => 10, 'offset' => 0, 'customer' => 'cus_123']
);
```

### Como montar `payload`

Body JSON:

```php
$result = $asaas->customer->createNewCustomer(
    payload: [
        'name' => 'João da Silva',
        'cpfCnpj' => '00586050000100',
        'email' => 'financeiro@cliente.com.br',
    ]
);
```

## Inventário REAL de métodos (fonte: código gerado)

A documentação anterior listava alguns serviços como “sem métodos”. Hoje isso **não é verdade**: a versão atual do repositório contém centenas de operações geradas em `src/Service/Generated/`.

✅ A referência completa (por serviço) está aqui:

- **[99 — Referência de endpoints (gerada do código)](99-reference-endpoints.md)**

Ela reflete **exatamente** os métodos disponíveis nesta versão do repositório.

## Convenção de nomes (por que os métodos são “verbosos”?)

Os nomes seguem o `operationId` do OpenAPI (ex.: `createNewCustomer`, `retrieveASingleCustomer`).
Isso melhora paridade com o spec, mas pode ficar menos “bonito” no uso manual.

**Recomendação prática:** no seu ERP/Perfex, crie uma camada de *facade local* (serviço interno) com nomes de negócio (`ensureCustomer`, `createBoleto`, `cancelPayment`, etc.), e deixe a SDK como transporte/contrato.

Exemplos dessa camada estão em:
- [11 — Exemplos Laravel](11-exemplos-laravel.md)
- [15 — Exemplos Perfex CRM](15-exemplos-perfex-crm.md)
