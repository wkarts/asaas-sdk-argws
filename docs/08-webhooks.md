# 08 — Webhooks

## Cadastro e atualização pela API

O serviço `$asaas->webhook` já oferece `createNewWebhook`, `listWebhooks`, `retrieveASingleWebhook`, `updateExistingWebhook`, `removeWebhook` e `removeWebhookBackoff`. As assinaturas existentes permanecem iguais: `pathParams`, `query`, `headers` e `payload`.

O token do webhook é o campo **`authToken` do payload de cadastro**. Ao entregar eventos, o Asaas envia esse valor no cabeçalho **`asaas-access-token`**. Ele é independente da API Key enviada pelo cliente da SDK no cabeçalho `access_token` das chamadas à API.

### Gerar e informar um token

```php
use Asaas\Sdk\AsaasSdk;
use Asaas\Sdk\Config\AsaasConfig;
use Asaas\Sdk\Http\Environment;
use Asaas\Sdk\Webhook\WebhookToken;

$asaas = new AsaasSdk(new AsaasConfig(
    apiKey: getenv('ASAAS_API_KEY') ?: '',
    environment: Environment::Sandbox,
    appName: 'MeuSistema/1.0'
));

$authToken = WebhookToken::generate();
// Guarde o token na configuração segura do receptor antes de ativar as entregas.

$webhook = $asaas->webhook->createNewWebhook(payload: [
    'name' => 'Eventos do CRM',
    'url' => 'https://crm.example.com/webhooks/asaas',
    'email' => 'webhooks@example.com',
    'enabled' => true,
    'interrupted' => false,
    'authToken' => $authToken,
    'events' => ['PAYMENT_RECEIVED', 'PAYMENT_CONFIRMED'],
    'sendType' => 'SEQUENTIALLY',
    'apiVersion' => 3,
]);
```

`WebhookToken::generate()` usa 32 bytes aleatórios criptograficamente seguros e retorna 64 caracteres hexadecimais. A geração ocorre somente quando a aplicação chama o método; o SDK não cria nem troca tokens durante consultas ou chamadas existentes.

Para um token informado manualmente, use `WebhookToken::isValid($token)`. A validação verifica de 32 a 255 caracteres, rejeita espaços e caracteres de controle e impede o prefixo de API Key `$aact_`. Isso verifica o formato, não a imprevisibilidade de uma senha escolhida manualmente. Prefira o gerador e nunca reutilize uma API Key.

### Token gerado pelo Asaas

Se `authToken` for omitido no cadastro, o Asaas gera um token e o devolve somente na resposta de criação. A SDK preserva esse retorno integralmente. Armazene `authToken` com segurança antes de consumir eventos; não o envie a logs ou a páginas públicas.

A SDK não preenche automaticamente um `authToken` ausente, não remove o token da resposta e não adiciona validação obrigatória às assinaturas dos serviços existentes. Os helpers são opcionais e aditivos.

### Alterar somente o token

```php
$novoToken = WebhookToken::generate();

$asaas->webhook->updateExistingWebhook(
    pathParams: ['id' => $webhook['id']],
    payload: ['authToken' => $novoToken]
);
```

O método transmite somente os campos fornecidos. Essa chamada não acrescenta nem substitui `events`, `name`, `url` ou outras configurações. Coordene a atualização do token no receptor e no Asaas para que entregas durante a troca possam ser repetidas com sucesso.

## Autenticar o recebimento

Valide o token antes de ler e processar o conteúdo do evento. Exemplo para o início de um controller Laravel:

```php
use Asaas\Sdk\Webhook\WebhookToken;
use Illuminate\Http\Request;

// $request é a requisição recebida pelo seu controller.
// services.asaas.webhook_token deve ler a configuração segura do receptor.
$expected = config('services.asaas.webhook_token', '');
$provided = $request->header('asaas-access-token');

if (!WebhookToken::matches($expected, $provided)) {
    return response()->json(['error' => 'Token inválido.'], 401);
}

// Continue aqui com o processamento já existente da aplicação.
// Persista o evento de forma idempotente antes de confirmar HTTP 200.
```

`WebhookToken::matches()` usa `hash_equals`, rejeita segredo esperado vazio e valores de tipos incorretos e compara o conteúdo exato, sem remover espaços ou alterar maiúsculas/minúsculas. Ele também compara tokens legados curtos: as regras de geração de novos tokens não invalidam automaticamente credenciais previamente configuradas.

O token deve trafegar no cabeçalho. Não o acrescente à URL do webhook, não o confunda com assinatura HMAC e não registre seu valor. Para mascarar uma coleção de cabeçalhos antes de registrar ou exibir dados, use `WebhookToken::redactHeaders($headers)`. O helper reconhece `asaas-access-token` sem diferenciar maiúsculas/minúsculas, preserva os demais cabeçalhos e suporta valores simples ou arrays.

## Playground

O endpoint existente continua sendo `POST /webhooks/asaas`. Configure `ASAAS_WEBHOOK_TOKEN` com o mesmo token cadastrado no Asaas.

- Com token configurado, token ausente, diferente ou repetido em múltiplos valores de cabeçalho retorna HTTP 401 antes da leitura do corpo ou da persistência pelo controlador.
- Com token correto, a resposta mantém `validated_token: true`.
- Com `ASAAS_WEBHOOK_TOKEN` vazio, o modo legado permanece habilitado com `validated_token: false`; isso significa que o evento **não foi autenticado por token**.
- O cabeçalho de token é mascarado antes de salvar novos registros. A tela de detalhes também mascara esse cabeçalho ao exibir registros de versões antigas, sem alterar o payload nem apagar o histórico.
- As respostas persistidas no histórico de chamadas do Explorer, cliente RAW e cenários usam o mascaramento existente de segredos. O `authToken` continua disponível na resposta imediata de criação para o consumidor armazená-lo, mas não é copiado para esses novos logs.

O Playground mantém o middleware geral de leitura de requisições do Slim. A validação antecipada acima se aplica ao controlador de recebimento; não representa uma mudança na ordem global dos middlewares.

## Entrega e idempotência

Use o `id` do evento para reconhecer reenvios. A autenticação por token identifica a credencial do remetente, mas não substitui a idempotência. Persista o evento antes de responder HTTP 200 e processe tarefas demoradas de forma assíncrona. Sandbox e Produção possuem configurações independentes.

## Referências oficiais

- [Receba eventos do Asaas no seu endpoint de Webhook](https://docs.asaas.com/docs/receba-eventos-do-asaas-no-seu-endpoint-de-webhook)
- [Criar novo Webhook](https://docs.asaas.com/reference/create-new-webhook)
- [Atualizar Webhook existente](https://docs.asaas.com/reference/update-existing-webhook)

## Testes locais sem acesso à conta Asaas

```bash
composer test -- --filter Webhook
php playground/tests/webhook-smoke.php
```

Os testes de transporte usam `Guzzle MockHandler`. Os testes do Playground podem ser executados após instalar as dependências próprias dele:

```bash
cd playground
composer install
vendor/bin/phpunit --bootstrap vendor/autoload.php tests
```
