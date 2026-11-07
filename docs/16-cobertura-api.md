# Cobertura e expansão da API — 2026-11-06

Base: `170831639665082c3bf50422877942b04a4a5315` (PR 21). O inventário abaixo conta 207 operações em 33 famílias no OpenAPI versionado (204 na base + 3 novas), não funcionalidades homologadas. A pesquisa priorizou contratos de pagamentos e Pix Automático, confrontou as famílias com o catálogo oficial e registrou limites. **Não foi auditado individualmente o contrato remoto de todos os endpoints.** Os índices `reference/llms.txt` e `docs/llms.txt` não ficaram acessíveis pela ferramenta de pesquisa; portanto não se declara cobertura completa da documentação atual.

[Índice oficial](https://docs.asaas.com/llms.txt), [referência](https://docs.asaas.com/reference/comece-por-aqui) e [catálogo oficial de funcionalidades e limites de sandbox](https://docs.asaas.com/docs/o-que-pode-ser-testado).

## Inventário reproduzível

O arquivo [api-endpoint-inventory.json](api-endpoint-inventory.json) lista método, caminho, operação e fonte por família. Links derivados de operationId são candidatos à referência, não comprovação de revisão individual. Contagens incluem aliases históricos do snapshot. Os wrappers recebem arrays e repassam campos novos; ausência de DTO específico não significa ausência de transporte.

| Família do snapshot | Operações | Situação nesta entrega |
| --- | ---: | --- |
| Customer | 7 | Existente; proteção de escrita ampliada; contrato não integralmente revalidado |
| Payment | 19 | Existente; proteção de escrita ampliada; contrato não integralmente revalidado |
| Payment with summary data | 11 | Existente; proteção de escrita ampliada; contrato não integralmente revalidado |
| Payment Document | 5 | Existente; proteção de escrita ampliada; contrato não integralmente revalidado |
| Installment | 10 | Existente; proteção de escrita ampliada; contrato não integralmente revalidado |
| Subscription | 14 | Existente; proteção de escrita ampliada; contrato não integralmente revalidado |
| Payment Link | 11 | Existente; proteção de escrita ampliada; contrato não integralmente revalidado |
| Checkout | 2 | Existente; proteção de escrita ampliada; contrato não integralmente revalidado |
| Anticipation | 8 | Existente; proteção de escrita ampliada; contrato não integralmente revalidado |
| Credit Card | 1 | Existente; proteção de escrita ampliada; contrato não integralmente revalidado |
| Payment Refund | 2 | Existente; proteção de escrita ampliada; contrato não integralmente revalidado |
| Payment Split | 4 | Existente; proteção de escrita ampliada; contrato não integralmente revalidado |
| Chargeback | 3 | Existente; proteção de escrita ampliada; contrato não integralmente revalidado |
| Escrow Account | 6 | Existente; proteção de escrita ampliada; contrato não integralmente revalidado |
| Notification | 2 | Existente; proteção de escrita ampliada; contrato não integralmente revalidado |
| Payment Dunning | 9 | Existente; proteção de escrita ampliada; contrato não integralmente revalidado |
| Credit Bureau Report | 3 | Existente; proteção de escrita ampliada; contrato não integralmente revalidado |
| Invoice | 6 | Existente; proteção de escrita ampliada; contrato não integralmente revalidado |
| Fiscal Info | 10 | Existente; proteção de escrita ampliada; contrato não integralmente revalidado |
| Pix | 7 | Existente; proteção de escrita ampliada; contrato não integralmente revalidado |
| Pix Transaction | 5 | Existente; proteção de escrita ampliada; contrato não integralmente revalidado |
| Recurring Pix | 5 | Existente; proteção de escrita ampliada; contrato não integralmente revalidado |
| Automatic Pix | 7 | 6 existentes agora expostas na fachada; 1 retentativa acrescentada; contrato de criação atualizado |
| Bill | 5 | Existente; proteção de escrita ampliada; contrato não integralmente revalidado |
| Transfer | 5 | Existente; proteção de escrita ampliada; contrato não integralmente revalidado |
| Mobile Phone Recharge | 5 | Existente; proteção de escrita ampliada; contrato não integralmente revalidado |
| Finance | 3 | Existente; proteção de escrita ampliada; contrato não integralmente revalidado |
| Account info | 9 | Existente; proteção de escrita ampliada; contrato não integralmente revalidado |
| Financial Transaction | 1 | Existente; proteção de escrita ampliada; contrato não integralmente revalidado |
| Webhook | 6 | Existente; proteção de escrita ampliada; contrato não integralmente revalidado |
| Subaccount | 9 | 7 existentes; reenvio de ativação e encerramento não-BaaS acrescentados |
| Account Document | 5 | Existente; proteção de escrita ampliada; contrato não integralmente revalidado |
| Sandbox Actions | 2 | Existente; proteção de escrita ampliada; contrato não integralmente revalidado |

`PaymentService::payAChargeWithCard` permanece uma extensão manual fora do snapshot, conforme PR 21. `createHostedCardPayment` é uma conveniência nova sobre `POST /v3/payments`, não um endpoint adicional.

## Contratos revisados e entregues

| Recurso | Antes | Agora / fonte oficial |
| --- | --- | --- |
| Crédito e voucher diretos | PR 21 já entrega payWithCard CREDIT/VOUCHER e payWithCreditCard | Preservados, incluindo testes de não repetição; não existe inferência de DEBIT a partir de enum de resposta |
| Débito pela fatura | Possível pela criação genérica, sem orientação específica | `createHostedCardPayment`: CREDIT_CARD padrão ou UNDEFINED; recusa campos de cartão/token e devolve invoiceUrl; [guia](https://docs.asaas.com/docs/cobrancas-via-cartao-de-credito) |
| Pix Automático | Seis métodos gerados sem propriedade na fachada | `$sdk->automaticPix` expõe criação/listagem/consulta/cancelamento de autorização e listagem/consulta de instruções; [criação](https://docs.asaas.com/reference/criar-uma-autorizacao-pix-automatico) |
| Geração dos ciclos | Snapshot não documentava modos atuais | paymentCreationMode MANUAL/SUBSCRIPTION e retryPolicy no snapshot; subscriptionId na consulta; [listagem](https://docs.asaas.com/reference/listar-autorizacoes-pix-automatico) |
| Retentativa financeira Pix | Ausente | POST `/v3/pix/automatic/paymentInstructions/{id}/retries`, dueDate, resposta 201; [contrato](https://docs.asaas.com/reference/criar-retentativa-de-instrucao-de-pagamento-pix-automatico) |
| Segurança de transporte | Proteção limitada a cartões | Somente GET/HEAD/OPTIONS repetem automaticamente; escritas falham explicitamente para conciliação |
| Checkout hospedado | Criar/cancelar já existem | Mantidos; CREDIT_CARD/PIX são os billingTypes documentados, não equivalentes à fatura; [contrato](https://docs.asaas.com/reference/criar-novo-checkout) |
| Pix dinâmico/estático | Já existem | Métodos preservados; QR dinâmico depende da cobrança, QR estático pode originar cobrança após recebimento; [dinâmico](https://docs.asaas.com/reference/obter-qr-code-para-pagamentos-via-pix), [estático](https://docs.asaas.com/reference/criar-qrcode-estatico) |

## Outros recursos confirmados

Dois contratos ausentes foram adicionados ao snapshot e ao serviço gerado `subaccount`:

- `resendNonBaasSubaccountActivationLink`: POST `/v3/accounts/{id}/resendActivationLink`, corpo vazio, resposta 204 normalizada como `[]`. Envia ao e-mail de login cadastrado e só pode ser usado uma vez por subconta. [Fonte](https://docs.asaas.com/reference/reenviar-link-de-ativacao-para-subconta-nao-baas).
- `closeNonBaasSubaccount`: DELETE `/v3/accounts/{id}`, irreversível. Requer conta-pai, não-BaaS, nunca ter feito login, menos de 48 horas de criação, habilitação Web e whitelist. Apenas o transporte foi implementado; nenhuma conta foi encerrada. [Fonte](https://docs.asaas.com/reference/encerrar-subconta-nao-baas).

A pesquisa também confirmou consultas fiscais já presentes: [serviços federais](https://docs.asaas.com/reference/listar-codigos-servicos-federais), [NBS](https://docs.asaas.com/reference/listar-codigos-nbs), [situação tributária](https://docs.asaas.com/reference/listar-codigos-de-situacoes-tributarias) e [classificação tributária](https://docs.asaas.com/reference/listar-codigos-de-classificacoes-tributarias). Não foi necessário duplicar seus endpoints. A escolha tributária cabe à aplicação e às regras fiscais da operação.

## Oportunidades e pendências explícitas

- Auditoria endpoint a endpoint dos campos de clientes, parcelamentos, assinaturas, links, antecipação, estornos, split, escrow, documentos, negativação, Serasa, notas fiscais, configurações municipais, contas, extrato, transferências, recargas e webhooks: as famílias já têm transporte, mas precisam de testes de contrato específicos antes de alegar paridade atual.
- Cobranças resumidas e criação de assinaturas com cartão também se beneficiam da proteção global; homologação de adquirência, elegibilidade e permissões continua dependente do Asaas.
- Campos de resposta novos do Pix Automático continuam acessíveis no array bruto; DTOs completos e máquina de estados não foram acrescentados. Nenhum estado é convertido em confirmação financeira pela SDK.
- Snapshot de `playground/public/openapi.json` permanece independente e não foi atualizado; a nova fachada é da biblioteca. A UI do playground e documentação de exemplos dela exigem atualização própria.
- Gerador atual extrai links HTML e ignora páginas sem OpenAPI; uma atualização completa deve comparar inventários e falhas antes de substituir o snapshot. O build padrão continua usando o snapshot fixado, sem acesso à API.
- CI de branches e PRs agora não recebe ASAAS_API_KEY; o comportamento de integração da branch padrão permanece separado. Sandbox e produção não foram acessados, nem foram usadas credenciais reais. Mocks comprovam roteamento, serialização e segurança HTTP; não homologam regras bancárias.

## Compatibilidade e versão

Métodos existentes e assinaturas preservados. A fachada ganha `automaticPix`; o novo helper é opcional. A mudança comportamental intencional é não repetir POST/PUT/PATCH/DELETE automaticamente. Aplicações que dependiam desse comportamento devem conciliar pelo identificador retornado, consulta e webhook antes de uma tentativa explícita; timeout pode significar operação concluída.

Versão sugerida: **0.3.0**, como incremento minor a partir de 0.2.64, destacando a alteração de retry. Sem tag/release nesta entrega. Rollout: atualizar ambiente de teste e executar os mocks; depois homologar fluxos autorizados separadamente. Rollback: fixar a versão anterior restaura o comportamento anterior, inclusive seus riscos de repetição; não desfaz operações já aceitas pelo Asaas.

## Leituras complementares e lacunas reais

A documentação de [consulta de cobranças](https://docs.asaas.com/reference/listar-cobrancas), [links de pagamento](https://docs.asaas.com/reference/listar-links-de-pagamentos), [transferências](https://docs.asaas.com/reference/listar-transferencias) e [recargas](https://docs.asaas.com/reference/solicitar-recarga) confirma famílias já presentes. A [consulta de provedor de recarga](https://docs.asaas.com/reference/buscar-qual-provedor-o-numero-pertence-e-os-valores-disponiveis-para-recarga) também existe; consultá-la não inicia uma recarga.

[Antecipações](https://docs.asaas.com/reference/solicitar-antecipacao) e [simulações](https://docs.asaas.com/reference/simular-antecipa%C3%A7%C3%A3o) já têm wrappers, mas a aceitação depende de elegibilidade e documentação. [Disputas de chargeback](https://docs.asaas.com/reference/create-a-chargeback-dispute) não garantem reversão. [Escrow](https://docs.asaas.com/reference/criar-configuracao-padrao-da-conta-escrow-para-todas-as-subcontas) pode liberar garantias ao ser desabilitado; não é um recurso a ativar ou testar indiscriminadamente. Por isso esta expansão protege todas as escritas e não executa demonstrações financeiras.


## Validação desta entrega

Em PHP 8.5.12 com PHPUnit 10.5.66: 26 testes unitários e 224 assertions aprovados, incluindo os testes de cartão da PR 21. PHPStan 1.12.34 sem erros; PHP CS Fixer 3.95.28 sem alterações em 134 arquivos; paridade aprovada; smoke offline de webhook com 49 verificações. Geração repetida determinística, inventário conferido contra o snapshot e `git diff --check` aprovado.

O host não tinha PHP; os testes rodaram em Docker. A instalação padrão encontrou bloqueio de rede para `api.github.com`; a instalação local usou os mesmos commits dos pacotes via arquivos do domínio permitido `codeload.github.com`, mantendo proxy e validação TLS. O lock temporário não integra a entrega. CI deve validar separadamente a matriz PHP 8.1/8.2/8.3. Nenhum teste financeiro real foi executado.
