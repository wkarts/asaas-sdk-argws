# 16 — Playground (referência de uso)

O Playground existe para **validar** e **explorar** a SDK sem precisar escrever código toda hora.
Ele não substitui sua aplicação: ele ajuda a descobrir métodos, testar payloads e simular cenários.

> Esta página foca no uso do Playground. Não cobre CI/CD, workflows nem publicação de imagem.

---

## 1) Conceito central: Explorer universal

O Explorer permite executar **qualquer método público** da SDK por Reflection.

- Você escolhe o **service** (ex.: `customer`, `payment`, `webhook`)
- Escolhe o **método** (ex.: `createNewCustomer`)
- Informa parâmetros em JSON

### Formatos aceitos para os parâmetros

**A) Por nome (recomendado)**

```json
{
  "pathParams": {"id": "cus_123"},
  "query": {"limit": 10},
  "headers": {},
  "payload": {"name": "João", "cpfCnpj": "00586050000100"}
}
```

**B) Posicional**

```json
[
  {"id": "cus_123"},
  {"limit": 10},
  {},
  {"name": "João"}
]
```

---

## 2) Headers especiais do Playground

Sem reiniciar o container, você pode sobrescrever por request:

- `X-Asaas-Api-Key`: usa a key informada no topo da UI (não persiste em disco)
- `X-Asaas-Env`: `sandbox` ou `production`

Isso é ideal para testar chaves de clientes diferentes rapidamente.

---

## 3) Catálogo (para automação)

Endpoint:

```
GET /sdk/catalog
```

Retorna JSON com:
- serviços disponíveis
- métodos e assinaturas
- (em geral) uma visão do que está “exposto” publicamente

Use isso para:
- gerar docs internas
- buildar “telas” no seu ERP
- validar paridade

---

## 4) Webhooks (validação rápida)

Endpoint:

```
POST /webhooks/asaas
```

Se você configurou `ASAAS_WEBHOOK_TOKEN`, o header `asaas-access-token` é obrigatório.

**Dica de produção:** no seu ERP/Perfex, trate webhook como **entrada idempotente**:
- guarde o `payment.id`
- guarde o `event`
- ignore duplicados

---

## 5) Logs (por que isso é útil?)

O Playground registra chamadas e respostas (removendo campos sensíveis).

Use para:
- copiar payload que deu certo
- ver exatamente `path/query/payload`
- depurar erro 400/401/500 sem abrir o Postman

---

## 6) “Como eu descubro qual método usar?”

1. Comece pela **Referência gerada do código**:  
   **[99 — Referência de endpoints (gerada do código)](99-reference-endpoints.md)**

2. Depois, no Playground:
   - vá no Explorer
   - busque pelo service + endpoint que você quer
   - rode com payload mínimo
