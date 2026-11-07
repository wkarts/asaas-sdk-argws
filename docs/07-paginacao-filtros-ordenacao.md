# 07 — Paginação, filtros e ordenação

A SDK recebe filtros como `array` e os envia à API. Use o argumento nomeado **`query:`**: nos métodos gerados, o primeiro argumento é `pathParams`, reservado aos identificadores inseridos no caminho. `listPayments($query)` não aplica esses filtros.

```php
$result = $asaas->payment->listPayments(query: [
    'limit' => 100,
    'offset' => 0,
    'customer' => 'cus_123',
    'status' => 'RECEIVED',
    'paymentDate[ge]' => '2026-10-01',
    'paymentDate[le]' => '2026-10-31',
]);
```

`Query::normalize` remove valores nulos e envia booleanos como `true`/`false`. Os filtros com colchetes são preservados. Ordenação e filtros variam por recurso; consulte a [matriz de relatórios](20-relatorios-financeiros.md). Não envie `sort` ou filtros de período a um endpoint que não os documenta.

## Percorrer uma listagem sem acumular todos os registros

O [padrão de paginação do Asaas](https://docs.asaas.com/reference/listagem-e-paginacao) usa `offset`, `limit` de 1 a 100 e `hasMore`. A API retorna uma página, não necessariamente todos os registros. O utilitário opcional `Paginator` retorna um `Generator` e consulta cada página apenas durante a iteração:

```php
use Asaas\Sdk\Util\Paginator;

$items = Paginator::items(
    fn(array $query) => $asaas->financialTransaction->retrieveExtract(query: $query),
    ['startDate' => '2026-10-01', 'finishDate' => '2026-10-31', 'order' => 'asc', 'limit' => 100],
    maxPages: 1000,
);

foreach ($items as $transaction) {
    // Processar um lançamento por vez; selecionar campos apropriados ao relatório.
}
```

Use `Paginator::pages(...)` com os mesmos argumentos para receber o envelope completo de cada página, incluindo `totalCount`, `offset`, `limit` e campos adicionais retornados pela API. O limite padrão é 100 registros por página e 1.000 páginas por execução; esses valores podem ser ajustados explicitamente dentro das regras da API. `limit`, `offset` e `maxPages` devem ser inteiros.

O próximo `offset` avança pela quantidade de registros recebidos, inclusive quando a página vem menor que o limite solicitado e ainda informa `hasMore: true`. Quando a API fornece `totalCount`, o paginador também confere sua consistência com a posição, os registros recebidos e a indicação de continuação.

O callback deve chamar **somente uma listagem GET**. O utilitário não executa novas tentativas de escrita, não altera status de cobranças e não substitui os webhooks. Respostas sem o envelope `data`/`hasMore` (por exemplo, agregados ou arrays de estornos) não devem ser passadas ao paginador.

## Falhas e exportações completas

`PaginationException` indica página inválida, `hasMore` sem registros, repetição da página anterior, offset ou total inconsistentes, ou limite de páginas atingido antes do fim. `getOffset()` informa a página ainda não processada. Erros HTTP e de transporte continuam sendo as exceções originais da SDK.

O limite de execução **não produz um relatório parcial marcado como completo**. Gere o arquivo em área temporária, consuma o iterador até o fim e só então disponibilize a exportação. Se houver uma exceção, descarte o arquivo parcial e informe a falha; para retomar um trabalho, mantenha também filtros, ambiente e identificadores já processados. A ordenação por offset não é um snapshot transacional: mudanças simultâneas na conta podem deslocar registros. Para conciliação, deduplique pelo ID e use janelas de consulta com sobreposição controlada.

O utilitário mantém somente a página corrente e a impressão digital da página anterior na memória. A aplicação continua responsável pelo prazo de execução, seleção de campos, proteção de dados, autorização do usuário e proteção contra fórmulas em CSV. Nenhum arquivo, log, cookie ou cache é criado pelo paginador.
