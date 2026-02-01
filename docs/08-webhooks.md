# 08 — Webhooks

## Cadastro de webhooks

O SDK possui a classe `WebhookService`, porém **ainda sem métodos gerados** no código atual.  
Para criar webhooks via API, aguarde a geração ou utilize um cliente HTTP próprio apontando para o endpoint da documentação oficial.

## Recebendo webhooks (Laravel)

```php
<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class AsaasWebhookController
{
    public function handle(Request $request)
    {
        $payload = $request->all();

        // Idempotência: use um eventId se existir no payload
        $eventId = $payload['id'] ?? null;

        Log::info('Asaas webhook recebido', [
            'eventId' => $eventId,
            'type' => $payload['event'] ?? null,
        ]);

        // Processar conforme o tipo
        return response()->json(['received' => true]);
    }
}
```

## Boas práticas

- Armazene `eventId` para evitar processamento duplicado.
- Verifique assinatura (se sua implementação de webhook fornecer esse recurso).
- Registre métricas e tempo de processamento.
