# Relatórios financeiros e recuperação por API

Auditoria documental: 2026-11-07. Base: SDK `v0.3.0` (`d0ede713d67d4a94d4edde66c0ff19fa4e2a4a7c`). Os métodos abaixo já estavam disponíveis nessa versão. Esta atualização acrescenta paginação opcional, corrige exemplos que ignoravam os filtros e completa os filtros de instruções Pix no contrato versionado. Não cria endpoints financeiros inexistentes nem modifica os métodos públicos existentes.

## Matriz de consultas

Todos os métodos desta tabela fazem **GET**. Utilize `query:` para filtros e `['id' => $id]` para parâmetros de caminho. Nas listas paginadas, percorra `hasMore`; `offset` e `limit` de 1 a 100 seguem o [padrão da API](https://docs.asaas.com/reference/listagem-e-paginacao).

| Consulta | Fachada e método | Caminho | Filtros documentados relevantes / limites |
| --- | --- | --- | --- |
| [Extrato da conta Asaas](https://docs.asaas.com/reference/recuperar-extrato) | `financialTransaction->retrieveExtract` | `/v3/financialTransactions` | `startDate`, `finishDate`, `order`, `offset`, `limit`. Cada item é um lançamento; cobrança e tarifa podem gerar linhas diferentes. |
| [Saldo disponível atual](https://docs.asaas.com/reference/recuperar-saldo-da-conta) | `finance->retrieveAccountBalance` | `/v3/finance/balance` | Sem filtros de período. Não representa saldo histórico de fechamento. |
| [Totais e quantidade de cobranças](https://docs.asaas.com/reference/estatisticas-de-cobrancas) | `finance->billingStatistics` | `/v3/finance/payment/statistics` | `customer`, `billingType`, `status`, `anticipated`, `externalReference`, `dateCreated[ge/le]`, `dueDate[ge/le]`, `estimatedCreditDate[ge/le]`. Agregado, sem paginação. |
| [Consolidação de split](https://docs.asaas.com/reference/recuperar-valores-de-split) | `finance->retrieveSplitValues` | `/v3/finance/split/statistics` | Sem filtros. `income` a receber; `value` a enviar. Use as listas para recortes. |
| [Cobranças, recebimentos e valores a receber](https://docs.asaas.com/reference/listar-cobrancas) | `payment->listPayments` | `/v3/payments` | `customer`, `customerGroupName`, `billingType`, `status`, `subscription`, `installment`, `externalReference`, `anticipated`, `anticipable`, `invoiceStatus`, `pixQrCodeId`, `user`, `checkoutSession`; `paymentDate`, `estimatedCreditDate` e intervalos `[ge]`/`[le]` de `dateCreated`, `paymentDate`, `estimatedCreditDate`, `dueDate`; `offset`, `limit`. |
| [Splits pagos](https://docs.asaas.com/reference/listar-splits-pagos) | `paymentSplit->listPaidSplits` | `/v3/payments/splits/paid` | `paymentId`, `status`, `paymentConfirmedDate[ge/le]`, `creditDate[ge/le]`, `offset`, `limit`. |
| [Splits recebidos](https://docs.asaas.com/reference/listar-splits-recebidos) | `paymentSplit->listReceivedSplits` | `/v3/payments/splits/received` | Mesmos filtros de splits pagos. Estado de liquidação deve ser conferido por item. |
| [Transferências](https://docs.asaas.com/reference/listar-transferencias) | `transfer->listTransfers` | `/v3/transfers` | `dateCreated[ge/le]`, `transferDate[ge/le]`, `type`. A página descreve listagem paginada, mas não enumera `offset`/`limit` na ficha de query; confira o envelope e nunca aceite uma página repetida como completa. |
| [Antecipações](https://docs.asaas.com/reference/listar-antecipacoes) | `anticipation->listAnticipations` | `/v3/anticipations` | `payment`, `installment`, `status`, `offset`, `limit`. Não há filtro de período documentado nessa operação. Consultar não solicita antecipação. |
| [Transações Pix](https://docs.asaas.com/reference/listar-transacoes) | `pixTransaction->listTransactions` | `/v3/pix/transactions` | `status`, `type`, `endToEndIdentifier`, `offset`, `limit`. Sem filtro documentado por cliente, período ou `conciliationIdentifier`. |
| [Parcelamentos](https://docs.asaas.com/reference/listar-parcelamentos) | `installment->listInstallments` | `/v3/installments` | `offset`, `limit`. Para as cobranças de um parcelamento, prefira `payment->listPayments(query: ['installment' => $id])`. |
| [Assinaturas](https://docs.asaas.com/reference/listar-assinaturas) | `subscription->listSubscriptions` | `/v3/subscriptions` | `customer`, `customerGroupName`, `billingType`, `status`, `deletedOnly`, `includeDeleted`, `externalReference`, `order`, `sort`, `offset`, `limit`. |
| [Contas pagas/agendadas](https://docs.asaas.com/reference/listar-pagamento-de-contas) | `bill->listBillPayments` | `/v3/bill` | `status`, `offset`, `limit`. São contas pagas pela conta Asaas, não cobranças recebidas de clientes. Sem filtros de período documentados. |
| [Chargebacks](https://docs.asaas.com/reference/listar-chargebacks) | `chargeback->listChargebacks` | `/v3/chargebacks/` | `creditCardBrand`, `originDisputeDate[ge/le]`, `originTransactionDate[ge/le]`, `status`, `offset`, `limit`. |
| [Estornos de uma cobrança](https://docs.asaas.com/reference/retrieve-refunds-of-a-single-payment) | `paymentRefund->retrieveRefundsOfASinglePayment` | `/v3/payments/{id}/refunds` | Requer ID da cobrança. Não é uma lista global de estornos por data. `payment->listPayments` também retorna `refunds` por cobrança, quando disponíveis. |
| [Autorizações de Pix Automático](https://docs.asaas.com/reference/listar-autorizacoes-pix-automatico) | `automaticPix->listAutomaticPixAuthorizations` | `/v3/pix/automatic/authorizations` | `status`, `customerId`, `subscriptionId`, `offset`, `limit`. Criada não significa ativa. |
| [Instruções de Pix Automático](https://docs.asaas.com/reference/listar-instrucoes-de-pagamento-pix-automatico) | `automaticPix->listAutomaticPixPaymentInstructions` | `/v3/pix/automatic/paymentInstructions` | `authorizationId`, `customerId`, `paymentId`, `status`. A ficha não enumera `offset`/`limit`; o snapshot descreve envelope paginado. `DONE` não substitui a confirmação financeira da cobrança. |
| [Notas fiscais](https://docs.asaas.com/reference/listar-notas-fiscais) | `invoice->listInvoices` | `/v3/invoices` | `effectiveDate[Ge]`, `effectiveDate[Le]` (com `G`/`L` maiúsculos), `payment`, `installment`, `externalReference`, `status`, `customer`, `offset`, `limit`. Consulta não agenda nem emite nota fiscal. |
| [Negativações](https://docs.asaas.com/reference/listar-negativacoes) | `paymentDunning->listPaymentDunnings` | `/v3/paymentDunnings` | `requestStartDate`, `requestEndDate`, `status`, `type`, `payment`, `offset`, `limit`. Consulta não solicita negativação. |
| [Recargas de celular](https://docs.asaas.com/reference/listar-recargas-de-celular) | `mobilePhoneRecharge->listMobileRecharges` | `/v3/mobilePhoneRecharges` | `offset`, `limit`. Consulta de despesas já registradas; não solicita recarga. |

`[ge/le]` é uma abreviação da tabela: envie duas chaves independentes, por exemplo `dueDate[ge]` e `dueDate[le]`. Filtros não documentados não devem ser exibidos como se fossem aplicados remotamente; uma filtragem local precisa percorrer a origem inteira e declarar seu critério.

## Relatórios possíveis e distinções importantes

Não foi identificado, nas famílias auditadas, um endpoint genérico que produza todos os relatórios PDF/XLSX/CSV do painel Asaas. A aplicação monta e exporta relatórios a partir das consultas acima. O extrato corresponde à **conta Asaas**; a consulta não fornece extrato bancário de outras instituições.

Cobranças `CONFIRMED`, `RECEIVED`, pendências e vencidos são recortes diferentes. Não some `value`, `netValue`, saldo disponível e movimentações do extrato como se fossem a mesma medida. Tarifas e antecipações devem ser identificadas pelos lançamentos ou recursos próprios. O [extrato](https://docs.asaas.com/reference/recuperar-extrato) pode devolver novos tipos de lançamento: preserve valores desconhecidos em vez de interromper a importação.

Para estornos, percorra todos os itens de `refunds` e confira `status`: a mera presença do array não comprova devolução. `PENDING` e `CANCELLED` não equivalem a `DONE`. Veja [interpretação oficial de estornos](https://docs.asaas.com/docs/refunds).

## Recuperação sem webhook

As listagens servem para consulta histórica e conciliação após uma indisponibilidade. Use um intervalo explícito, percorra as páginas e relacione cobranças pelo ID, `externalReference`, cliente e vínculos já conhecidos. Número formatado da fatura Perfex e nome do cliente precisam ser resolvidos pela aplicação; `customer` na API é o ID Asaas, não o nome.

Consultar um recebimento não deve criar outro. A aplicação decide se registra uma baixa no CRM após validar cobrança, cliente, valor, ambiente e duplicidade. Consulta ou instrução Pix agendada não é prova de pagamento. Webhooks continuam sendo o canal de mudanças; o cron pode recuperar lacunas usando as mesmas regras idempotentes.

Para Pix Automático, o primeiro QR disponibiliza `immediateQrCode.conciliationIdentifier`; ele não deve ser tratado como ID da cobrança. O vínculo entre autorização, transação Pix e cobrança exige comparação de identificadores retornados e validação financeira. Não associe apenas por descrição ou valor. A autorização pode ser recusada após o recebimento inicial, sem invalidar aquele recebimento; veja a [FAQ oficial](https://docs.asaas.com/docs/faq-2).

## Implementação de exportação

Use [`Paginator`](07-paginacao-filtros-ordenacao.md) para listas com envelope, ou controle explicitamente o laço se o trabalho precisar de checkpoints por lote. Preserve os filtros em todas as requisições. Um limite de execução é um mecanismo de proteção, não uma confirmação de que todos os resultados foram lidos.

Grave em arquivo temporário e só entregue após conclusão. Registre o período, filtros, ambiente, horário de consulta e contagem efetivamente processada. Se a API falhar, repetir a página, ignorar paginação ou o prazo/limite ser alcançado, informe exportação incompleta e não entregue um arquivo parcial como relatório integral. CSV deve neutralizar células que possam ser interpretadas como fórmulas e conter apenas campos permitidos ao colaborador. Não exporte payloads brutos, dados de cartão, tokens ou chaves.

## Compatibilidade e validação

Esta auditoria valida caminhos, filtros e transporte por testes locais com respostas simuladas. Não executa cobranças, antecipações, transferências, estornos ou consultas reais de conta. Não declara homologação de toda a API. DTOs não foram introduzidos: os serviços continuam retornando arrays e preservando campos desconhecidos.

O snapshot de instruções Pix agora contempla os quatro filtros oficiais; as respostas de autorização incluem `paymentCreationMode`/`retryPolicy`; a consulta de contas pagas contempla `status`. A geração deve ser executada e permanecer determinística; as assinaturas geradas não dependem desses filtros e continuam iguais. A política de retentativa HTTP existente permanece limitada a GET/HEAD/OPTIONS.
