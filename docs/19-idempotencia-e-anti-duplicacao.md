# 22 - Idempotência e “não duplicar” como padrão (ERP/Perfex)

O objetivo: **você nunca criar 2 cobranças para o mesmo documento** e **nunca processar o mesmo webhook duas vezes**.

Asaas recomenda explicitamente usar o `id` único do evento como chave única de dedupe em banco: https://docs.asaas.com/docs/como-implementar-idempotencia-em-webhooks 

---

## 22.1 Idempotência na criação (create)

### Estratégia 1 — `externalReference` como chave de negócio
Quando o recurso suporta `externalReference`, use SEMPRE.

Padrão sugerido:
- ERP: `ERP:invoice:{id}`
- Perfex: `PERFEX:invoice:{id}`

**Regra**: um documento local → um `externalReference`.

### Estratégia 2 — “consulta antes de criar”
Quando seu volume for alto, você pode:
- Consultar customer por CPF/CNPJ
- Consultar payment por `externalReference` (quando disponível)
- Se existir, **reutiliza** o `asaas_payment_id` persistido localmente.

### Estratégia 3 — índice único local (última linha de defesa)
Mesmo que seu código falhe, o banco te protege:
- `unique(external_reference, tipo_documento)`
- `unique(asaas_payment_id)`

---

## 22.2 Idempotência em Webhooks (processamento)

### Regra de ouro (oficial)
“Eventos enviados pelos Webhooks do Asaas possuem IDs únicos… use este ID como chave única”. 

Ou seja:
- Crie uma tabela `asaas_webhook_events`
- Campo `event_id` UNIQUE
- Ao receber o webhook:
  - tenta inserir `event_id`
  - se falhar (duplicado) → **retorna 200 e encerra** (idempotente)

### Estrutura mínima sugerida (Laravel migration)
```php
Schema::create('asaas_webhook_events', function (Blueprint $table) {
    $table->id();
    $table->string('event_id', 64)->unique();
    $table->string('event', 64);
    $table->string('resource_id', 64)->nullable(); // ex.: payment.id
    $table->json('payload');
    $table->timestamps();
});
```

### Handler (Laravel) — esqueleto “à prova de repetição”
```php
public function handle(Request $request)
{
    $eventId = (string) $request->input('id');
    $event   = (string) $request->input('event');
    $payment = $request->input('payment'); // quando for webhook de cobrança

    try {
        WebhookEvent::create([
            'event_id'    => $eventId,
            'event'       => $event,
            'resource_id' => $payment['id'] ?? null,
            'payload'     => $request->all(),
        ]);
    } catch (QueryException $e) {
        // duplicado -> idempotência
        return response()->json(['ok' => true]);
    }

    // 1) Atualiza cobrança local pelo payment.id
    // 2) Aplica transição de status
    // 3) Dispara rotinas internas (baixa, conciliação, etc.)

    return response()->json(['ok' => true]);
}
```

---

## 22.3 “Dedupe no webhook” + “fonte da verdade”
Além do `event_id`:

- Sempre que possível, busque o registro local por `asaas_payment_id`.
- Se não existir, crie um “placeholder” (ex.: cobrança criada fora do ERP) e associe posteriormente.

---

## 22.4 Assinaturas: dedupe via cobrança
Asaas deixa claro: a gestão é por webhooks de cobrança e o webhook `PAYMENT_CREATED` traz o `subscription` id. 

Regra prática:
- `asaas_subscription_id` é preenchido/confirmado ao receber o `PAYMENT_CREATED` (ou na criação).
- As transições do contrato no ERP seguem os pagamentos recebidos.

---

## 22.5 Evite Polling como estratégia principal
O Asaas recomenda webhooks como forma mais prática/segura e cita limitações do polling: https://docs.asaas.com/docs/polling-vs-webhooks 

Use polling só como:
- “reconciliador noturno” (garantia), ou
- fallback em caso de indisponibilidade momentânea do endpoint de webhook.

