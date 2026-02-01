# 09 — Upload/Download de arquivos

## Upload (multipart)

A infraestrutura HTTP suporta multipart via `Client::formatMultipart`.  
Porém, **os serviços atuais não expõem métodos de upload gerados**.

Quando o método existir (ex.: envio de documentos), o padrão esperado é:

```php
$payload = [
    'type' => 'IDENTIFICATION',
    'file' => fopen('/caminho/documento.pdf', 'r')
];

// Exemplo ilustrativo: o método precisa existir no serviço gerado.
// $asaas->accountDocument->uploadAccountDocument($payload);
```

## Download (binary)

A SDK suporta respostas binárias internamente (ex.: PDF), retornando `string`.  
Contudo, **não há métodos públicos gerados no momento que retornem binário**.

Quando um método binário existir, salve assim:

```php
// $pdfContent = $asaas->paymentDocument->downloadPaymentDocument(['id' => 'doc_123']);
// file_put_contents('documento.pdf', $pdfContent);
```

> Consulte `docs/13-geracao-openapi-e-paridade.md` para gerar métodos a partir do OpenAPI.
