<?php

declare(strict_types=1);

namespace Playground\Tests;

use GuzzleHttp\Psr7\ServerRequest;
use PHPUnit\Framework\TestCase;
use Playground\Bootstrap;
use Playground\Controllers\WebhookController;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Psr7\Response;

final class WebhookReceiverTest extends TestCase
{
    private string $basePath;
    private string|false $previousToken;
    private Bootstrap $bootstrap;
    private WebhookController $controller;

    protected function setUp(): void
    {
        if (!extension_loaded('pdo_sqlite')) {
            self::markTestSkipped('PDO SQLite necessário para testar o receptor.');
        }

        $this->previousToken = getenv('ASAAS_WEBHOOK_TOKEN');
        putenv('ASAAS_WEBHOOK_TOKEN=' . str_repeat('a', 64));
        $this->basePath = sys_get_temp_dir() . '/asaas-webhook-test-' . bin2hex(random_bytes(6));
        mkdir($this->basePath . '/views', 0o700, true);
        copy(dirname(__DIR__) . '/views/webhooks.php', $this->basePath . '/views/webhooks.php');
        copy(dirname(__DIR__) . '/views/layout.php', $this->basePath . '/views/layout.php');
        $this->bootstrap = new Bootstrap($this->basePath);
        $this->controller = new WebhookController($this->bootstrap);
    }

    protected function tearDown(): void
    {
        if (!isset($this->basePath)) {
            return;
        }

        putenv($this->previousToken === false ? 'ASAAS_WEBHOOK_TOKEN' : 'ASAAS_WEBHOOK_TOKEN=' . $this->previousToken);
        $files = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($this->basePath, \FilesystemIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::CHILD_FIRST
        );
        foreach ($files as $file) {
            $file->isDir() ? rmdir($file->getPathname()) : unlink($file->getPathname());
        }
        rmdir($this->basePath);
    }

    public function testUnauthorizedRequestDoesNotReadBodyOrPersistEvent(): void
    {
        $request = $this->createMock(ServerRequestInterface::class);
        $request->method('getHeader')->with('asaas-access-token')->willReturn(['invalid']);
        $request->expects(self::never())->method('getBody');
        $request->expects(self::never())->method('getHeaders');

        $response = $this->controller->receive($request, new Response());

        self::assertSame(401, $response->getStatusCode());
        self::assertSame(0, (int) $this->bootstrap->db()->pdo()->query('SELECT COUNT(*) FROM webhooks')->fetchColumn());
    }

    public function testValidRequestPersistsPayloadButMasksAuthenticationHeader(): void
    {
        $token = str_repeat('a', 64);
        $payload = ['id' => 'evt_test', 'event' => 'PAYMENT_RECEIVED', 'payment' => ['id' => 'pay_test']];
        $request = new ServerRequest('POST', '/webhooks/asaas', [
            'Asaas-Access-Token' => $token,
            'X-Trace' => 'trace-test',
        ], json_encode($payload, JSON_THROW_ON_ERROR));

        $response = $this->controller->receive($request, new Response());
        $stored = $this->bootstrap->db()->pdo()->query('SELECT * FROM webhooks')->fetch(\PDO::FETCH_ASSOC);

        self::assertSame(200, $response->getStatusCode());
        self::assertSame(1, (int) $stored['validated_token']);
        self::assertSame($payload, json_decode($stored['payload_json'], true));
        self::assertStringNotContainsString($token, $stored['headers_json']);
        self::assertSame('[REDACTED]', json_decode($stored['headers_json'], true)['Asaas-Access-Token']);
        self::assertSame('trace-test', json_decode($stored['headers_json'], true)['X-Trace']);
    }

    public function testMultipleAuthenticationHeaderValuesAreRejected(): void
    {
        $request = new ServerRequest('POST', '/webhooks/asaas', [
            'asaas-access-token' => [str_repeat('a', 64), str_repeat('a', 64)],
        ], '{}');

        $response = $this->controller->receive($request, new Response());

        self::assertSame(401, $response->getStatusCode());
        self::assertSame(0, (int) $this->bootstrap->db()->pdo()->query('SELECT COUNT(*) FROM webhooks')->fetchColumn());
    }

    public function testLegacyDetailsAreMaskedWithoutChangingStoredHistory(): void
    {
        $token = 'legacy-record-secret';
        $headers = json_encode(['ASAAS-ACCESS-TOKEN' => $token, 'X-Trace' => 'old-trace'], JSON_THROW_ON_ERROR);
        $db = $this->bootstrap->db()->pdo();
        $insert = $db->prepare('INSERT INTO webhooks (received_at, headers_json, payload_json, validated_token) VALUES (?, ?, ?, ?)');
        $insert->execute([date('c'), $headers, '{"id":"evt_legacy"}', 1]);
        $id = $db->lastInsertId();

        $response = $this->controller->show(new ServerRequest('GET', '/webhooks/' . $id), new Response(), ['id' => $id]);
        $html = (string) $response->getBody();

        self::assertSame(200, $response->getStatusCode());
        self::assertStringNotContainsString($token, $html);
        self::assertStringContainsString('[REDACTED]', $html);
        self::assertStringContainsString('old-trace', $html);
        self::assertSame($headers, $db->query('SELECT headers_json FROM webhooks WHERE id = ' . (int) $id)->fetchColumn());
    }
}
