<?php

declare(strict_types=1);

use Asaas\Sdk\Http\Client;
use Asaas\Sdk\Http\Environment;
use Asaas\Sdk\Service\WebhookService;
use Asaas\Sdk\Webhook\WebhookToken;
use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\ServerRequest;
use Playground\Bootstrap;
use Playground\Controllers\WebhookController;
use Psr\Http\Message\StreamInterface;

require dirname(__DIR__, 2) . '/vendor/autoload.php';

spl_autoload_register(static function (string $class): void {
    if (str_starts_with($class, 'Playground\\')) {
        $path = dirname(__DIR__) . '/src/' . str_replace('\\', '/', substr($class, 11)) . '.php';
        if (is_file($path)) {
            require_once $path;
        }
    }
});

// Only the PSR-7 response is substituted when running with SDK dependencies alone.
// The receiver, token handling, SQLite persistence and template rendering remain real.
if (!class_exists(Slim\Psr7\Response::class)) {
    class_alias(Response::class, Slim\Psr7\Response::class);
}

$checks = 0;
$check = static function (bool $condition, string $message) use (&$checks): void {
    if (!$condition) {
        throw new RuntimeException($message);
    }
    ++$checks;
};

$token = WebhookToken::generate();
$check(preg_match('/\A[a-f0-9]{64}\z/', $token) === 1, 'Token gerado fora do formato esperado.');
$check($token !== WebhookToken::generate(), 'Geração repetiu o token.');
$check(WebhookToken::isValid($token), 'Token gerado inválido.');
$check(WebhookToken::isValid(str_repeat('a', 32)), 'Limite inferior inválido.');
$check(WebhookToken::isValid(str_repeat('a', 255)), 'Limite superior inválido.');
foreach ([null, [], '', str_repeat('a', 31), str_repeat('a', 256), str_repeat('a', 32) . ' ', str_repeat('a', 32) . "\n", str_repeat('a', 32) . "\0", str_repeat('a', 32) . "\u{00A0}", '$aact_' . str_repeat('a', 40)] as $invalid) {
    $check(!WebhookToken::isValid($invalid), 'Formato inválido aceito.');
}
$check(WebhookToken::matches('legacy-short', 'legacy-short'), 'Token legado deixou de autenticar.');
$check(!WebhookToken::matches('legacy-short', 'wrong'), 'Token incorreto autenticou.');
$check(!WebhookToken::matches('legacy-short', ['legacy-short']), 'Tipo incorreto autenticou.');
$check(!WebhookToken::matches('', ''), 'Segredo esperado vazio autenticou.');
$redacted = WebhookToken::redactHeaders(['Asaas-Access-Token' => [$token], 'X-Trace' => 'trace']);
$check($redacted === ['Asaas-Access-Token' => ['[REDACTED]'], 'X-Trace' => 'trace'], 'Mascaramento alterou outros cabeçalhos.');

$history = [];
$generatedByAsaas = 'whsec_' . str_repeat('b', 40);
$stack = HandlerStack::create(new MockHandler([
    new Response(200, [], json_encode(['id' => 'whk_1', 'authToken' => $token], JSON_THROW_ON_ERROR)),
    new Response(200, [], json_encode(['id' => 'whk_2', 'authToken' => $generatedByAsaas], JSON_THROW_ON_ERROR)),
    new Response(200, [], '{"id":"whk_1"}'),
]));
$stack->push(Middleware::history($history));
$client = new Client('api-key-for-test', Environment::Sandbox, 'WebhookSmoke/1.0', client: new GuzzleClient([
    'handler' => $stack,
    'base_uri' => Environment::Sandbox->value,
]));
$service = new WebhookService($client);
$payload = [
    'name' => 'CRM',
    'url' => 'https://crm.example.test/webhooks/asaas',
    'email' => 'webhooks@example.test',
    'enabled' => true,
    'interrupted' => false,
    'events' => ['PAYMENT_RECEIVED'],
    'sendType' => 'SEQUENTIALLY',
];
$created = $service->createNewWebhook(payload: $payload + ['authToken' => $token]);
$check($created['authToken'] === $token, 'Resposta de criação perdeu token fornecido.');
$check(json_decode((string) $history[0]['request']->getBody(), true) === $payload + ['authToken' => $token], 'Payload de criação alterado.');
$check($history[0]['request']->getHeaderLine('access_token') === 'api-key-for-test', 'API Key alterada.');
$check(!$history[0]['request']->hasHeader('asaas-access-token'), 'Token do webhook foi usado como credencial de API.');
$created = $service->createNewWebhook(payload: $payload);
$check($created['authToken'] === $generatedByAsaas, 'Resposta perdeu token gerado pelo Asaas.');
$check(json_decode((string) $history[1]['request']->getBody(), true) === $payload, 'Cadastro sem token foi alterado.');
$service->updateExistingWebhook(pathParams: ['id' => 'whk_1'], payload: ['authToken' => $token]);
$check(json_decode((string) $history[2]['request']->getBody(), true) === ['authToken' => $token], 'Atualização parcial modificou outros campos.');
$check($history[2]['request']->getMethod() === 'PUT' && $history[2]['request']->getUri()->getPath() === '/v3/webhooks/whk_1', 'Rota de atualização alterada.');

if (!extension_loaded('pdo_sqlite')) {
    throw new RuntimeException('Ative PDO SQLite para executar também as verificações do Playground.');
}

$previousToken = getenv('ASAAS_WEBHOOK_TOKEN');
$basePath = sys_get_temp_dir() . '/asaas-webhook-smoke-' . bin2hex(random_bytes(6));
mkdir($basePath . '/views', 0o700, true);
copy(dirname(__DIR__) . '/views/webhooks.php', $basePath . '/views/webhooks.php');
copy(dirname(__DIR__) . '/views/layout.php', $basePath . '/views/layout.php');

try {
    putenv('ASAAS_WEBHOOK_TOKEN=' . $token);
    $bootstrap = new Bootstrap($basePath);
    $controller = new WebhookController($bootstrap);
    $db = $bootstrap->db()->pdo();
    // Persisted call history must not keep the authToken returned at creation.
    $apiResponse = ['id' => 'whk_logged', 'authToken' => $token, 'nested' => ['authToken' => $generatedByAsaas]];
    foreach ([
        Playground\Controllers\ExplorerController::class,
        Playground\Controllers\RawController::class,
        Playground\Controllers\ScenariosController::class,
    ] as $controllerClass) {
        $loggingController = new $controllerClass($bootstrap);
        $logMethod = new ReflectionMethod($controllerClass, 'logAction');
        $arguments = ['WEBHOOK_TOKEN_TEST', [], 1, true];
        if ($controllerClass === Playground\Controllers\ExplorerController::class) {
            $arguments[] = 200;
        }
        $arguments[] = null;
        $arguments[] = $apiResponse;
        $logMethod->invokeArgs($loggingController, $arguments);
        $logged = (string) $db->query('SELECT response_excerpt FROM logs ORDER BY id DESC LIMIT 1')->fetchColumn();
        $check(!str_contains($logged, $token) && !str_contains($logged, $generatedByAsaas), 'Token vazou no histórico de chamadas.');
        $check(json_decode($logged, true)['id'] === 'whk_logged', 'Histórico perdeu dados não sensíveis.');
        $check($apiResponse['authToken'] === $token, 'A cópia destinada ao consumidor perdeu o token.');
    }

    $request = new class ('POST', '/webhooks/asaas', ['asaas-access-token' => 'wrong'], '{invalid-json') extends ServerRequest {
        public function getBody(): StreamInterface
        {
            throw new RuntimeException('Corpo lido antes de rejeitar a autenticação.');
        }
    };
    $response = $controller->receive($request, new Response());
    $check($response->getStatusCode() === 401, 'Requisição não autenticada não retornou 401.');
    $check((int) $db->query('SELECT COUNT(*) FROM webhooks')->fetchColumn() === 0, 'Evento não autenticado foi persistido.');

    $event = ['id' => 'evt_smoke', 'event' => 'PAYMENT_RECEIVED', 'payment' => ['id' => 'pay_smoke']];
    $request = new ServerRequest('POST', '/webhooks/asaas', ['Asaas-Access-Token' => $token, 'X-Trace' => 'trace'], json_encode($event, JSON_THROW_ON_ERROR));
    $response = $controller->receive($request, new Response());
    $stored = $db->query('SELECT * FROM webhooks')->fetch(PDO::FETCH_ASSOC);
    $check($response->getStatusCode() === 200, 'Evento autenticado foi rejeitado.');
    $check((int) $stored['validated_token'] === 1, 'Evento autenticado não foi marcado como validado.');
    $check(json_decode($stored['payload_json'], true) === $event, 'Payload do evento foi alterado.');
    $check(!str_contains($stored['headers_json'], $token), 'Token vazou no histórico de cabeçalhos.');
    $check(json_decode($stored['headers_json'], true)['X-Trace'] === 'trace', 'Outro cabeçalho foi removido.');

    $duplicateHeaders = new ServerRequest('POST', '/webhooks/asaas', ['asaas-access-token' => [$token, $token]], '{}');
    $check($controller->receive($duplicateHeaders, new Response())->getStatusCode() === 401, 'Múltiplos valores de token foram aceitos.');

    $legacyHeaders = json_encode(['ASAAS-ACCESS-TOKEN' => 'legacy-visible-secret', 'X-Trace' => 'old-trace'], JSON_THROW_ON_ERROR);
    $db->prepare('INSERT INTO webhooks (received_at, headers_json, payload_json, validated_token) VALUES (?, ?, ?, ?)')->execute([date('c'), $legacyHeaders, '{"id":"evt_old"}', 1]);
    $id = $db->lastInsertId();
    $details = $controller->show(new ServerRequest('GET', '/webhooks/' . $id), new Response(), ['id' => $id]);
    $html = (string) $details->getBody();
    $check(!str_contains($html, 'legacy-visible-secret') && str_contains($html, '[REDACTED]'), 'Detalhes vazaram token de registro legado.');
    $check(str_contains($html, 'old-trace'), 'Detalhes perderam cabeçalho não sensível.');
    $check($db->query('SELECT headers_json FROM webhooks WHERE id = ' . (int) $id)->fetchColumn() === $legacyHeaders, 'Histórico legado foi regravado.');

    putenv('ASAAS_WEBHOOK_TOKEN=');
    $response = $controller->receive(new ServerRequest('POST', '/webhooks/asaas', [], '{"id":"evt_no_token"}'), new Response());
    $legacyResult = json_decode((string) $response->getBody(), true);
    $check($response->getStatusCode() === 200 && $legacyResult['validated_token'] === false, 'Modo legado vazio deixou de ser explícito.');
} finally {
    putenv($previousToken === false ? 'ASAAS_WEBHOOK_TOKEN' : 'ASAAS_WEBHOOK_TOKEN=' . $previousToken);
    unset($db);
    $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($basePath, FilesystemIterator::SKIP_DOTS), RecursiveIteratorIterator::CHILD_FIRST);
    foreach ($files as $file) {
        $file->isDir() ? rmdir($file->getPathname()) : unlink($file->getPathname());
    }
    rmdir($basePath);
}

fwrite(STDOUT, 'Webhook smoke: ' . $checks . " verificações aprovadas; nenhuma chamada à conta Asaas.\n");
