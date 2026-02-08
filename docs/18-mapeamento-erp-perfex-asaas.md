# 21 - Mapeamento ERP/Perfex ↔ Asaas (campos e fluxos)

A ideia aqui é você **não “espalhar” decisões**: padronize o que você persiste localmente e como você transita os status.

---

## 21.1 Tabela de mapeamento — IDs e campos que você deve persistir

### Customer
| Local (ERP/Perfex) | Asaas | Observação |
|---|---|---|
| `asaas_customer_id` | `customer.id` | ID remoto “fonte da verdade” |
| `cpf_cnpj` | `customer.cpfCnpj` | Chave de busca padrão |
| `email` | `customer.email` | Importante p/ notificações |

### Payment (Cobrança)
| Local | Asaas | Por quê |
|---|---|---|
| `asaas_payment_id` | `payment.id` | Chave remota |
| `status` | `payment.status` | Controle do título/conta a receber |
| `billing_type` | `payment.billingType` | BOLETO / PIX / CREDIT_CARD |
| `value` | `payment.value` | Valor nominal |
| `net_value` | `payment.netValue` | Útil p/ conciliação |
| `due_date` | `payment.dueDate` | Vencimento |
| `invoice_url` | `payment.invoiceUrl` | Link de pagamento |
| `bank_slip_url` | `payment.bankSlipUrl` | Link boleto (quando existir) |
| `nosso_numero` | `payment.nossoNumero` | Cobrança bancária |
| `linha_digitavel` | `digitableLine` | Via endpoint “linha digitável” |
| `pix_qrcode_id` | `payment.pixQrCodeId` | Quando pago por QR Code (estático/dinâmico)  |
| `pix_transaction_id` | `payment.pixTransaction` | Quando houver transação Pix  |
| `external_reference` | `payment.externalReference` | **Seu dedupe padrão** (invoice_id, venda_id…) |

### Subscription (Assinatura)
| Local | Asaas | Observação |
|---|---|---|
| `asaas_subscription_id` | `subscription.id` | ID remoto |
| `external_reference` | `subscription.externalReference` | Ligação com contrato/plano |
| `billing_type` | `subscription.billingType` | Tipo base da cobrança |
| `value` | `subscription.value` | Valor recorrente |
| `cycle` | `subscription.cycle` | Mensal, semanal… |
| `next_due_date` | `subscription.nextDueDate` | Próxima geração |

> Importante: não existe “webhook de assinatura” isolado; o fluxo acontece via webhooks de cobrança e o evento `PAYMENT_CREATED` traz o `subscription` id. 

---

## 21.2 Fluxos “core” (ERP/Perfex)

### Fluxo A — Gerar cobrança (sem duplicar)
1) Resolver `asaas_customer_id` (consulta por CPF/CNPJ; cria se não existir).
2) Montar `externalReference = ERP:invoice:{id}` (ou equivalente).
3) **Antes de criar**: consultar cobranças por `externalReference` (quando seu fluxo permitir) e/ou manter índice único local.
4) Criar cobrança.
5) Persistir: `asaas_payment_id`, `status`, `invoice_url` e/ou `bank_slip_url`/PIX info.

### Fluxo B — Alterar cobrança (quando permitido)
A atualização é possível apenas em cobranças pendentes/vencidas (conforme referência): https://docs.asaas.com/reference/update-existing-payment.md   
No ERP/Perfex:
- Só permitir “editar” enquanto `status` ∈ {PENDING, OVERDUE}
- Caso contrário: gerar nova cobrança (ou aplicar política do seu negócio).

### Fluxo C — Baixar via Webhook (fonte da verdade)
1) Receber evento.
2) Dedupe por `event.id` (idempotência). 
3) Atualizar sua cobrança local pelo `payment.id`.
4) Se `PAYMENT_RECEIVED` / `PAYMENT_CONFIRMED`: marcar título como pago e salvar dados de conciliação.

### Fluxo D — Cancelar / Remover
- Use o endpoint de delete/remove do payment.
- No local: marcar como cancelado e preservar IDs para auditoria.

### Fluxo E — Reemitir
Normalmente “reemitir” vira:
- Atualizar vencimento se permitido; caso não permitido, **criar nova cobrança** com novo vencimento e link, mantendo referência do documento original.

---

## 21.3 “Pontos de amarração” recomendados (Laravel ERP / Perfex)

- `externalReference` sempre no formato: `ERP:<tipo>:<id>` (ex.: `ERP:invoice:287`).
- Persistir `asaas_*_id` em tabelas próprias (não em JSON solto).
- Criar índices únicos no banco local:
  - `unique(asaas_payment_id)`
  - `unique(external_reference)` por tipo/documento (quando fizer sentido)
  - `unique(asaas_webhook_event_id)` na tabela de fila de eventos.

