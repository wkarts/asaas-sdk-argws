<?php

declare(strict_types=1);

namespace Asaas\Sdk\Tests\Unit;

use Asaas\Sdk\Http\Client;
use Asaas\Sdk\Http\Environment;
use Asaas\Sdk\Service\WebhookService;
use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;

final class WebhookServiceTest extends TestCase
{
    public function testCreateSendsAuthTokenInPayloadAndPreservesResponse(): void
    {
        $token = str_repeat('a', 64);
        $payload = $this->creationPayload() + ['authToken' => $token];
        $response = ['id' => 'whk_created', 'authToken' => $token];
        $history = [];
        $service = $this->service($response, $history);

        self::assertSame($response, $service->createNewWebhook(payload: $payload));
        self::assertCount(1, $history);
        $request = $history[0]['request'];
        self::assertSame('POST', $request->getMethod());
        self::assertSame('/v3/webhooks', $request->getUri()->getPath());
        self::assertSame($payload, json_decode((string) $request->getBody(), true));
        self::assertSame('api-key-for-test', $request->getHeaderLine('access_token'));
        self::assertFalse($request->hasHeader('asaas-access-token'));
    }

    public function testOmittingAuthTokenPreservesAsaasGeneratedTokenInResponse(): void
    {
        $payload = $this->creationPayload();
        $response = ['id' => 'whk_generated', 'authToken' => 'whsec_' . str_repeat('b', 40)];
        $history = [];
        $service = $this->service($response, $history);

        self::assertSame($response, $service->createNewWebhook(payload: $payload));
        self::assertSame($payload, json_decode((string) $history[0]['request']->getBody(), true));
        self::assertArrayNotHasKey('authToken', $payload);
    }

    public function testUpdatingOnlyAuthTokenDoesNotReplaceEventsOrOtherSettings(): void
    {
        $payload = ['authToken' => str_repeat('c', 64)];
        $history = [];
        $service = $this->service(['id' => 'whk_existing'], $history);

        $service->updateExistingWebhook(pathParams: ['id' => 'whk_existing'], payload: $payload);

        $request = $history[0]['request'];
        self::assertSame('PUT', $request->getMethod());
        self::assertSame('/v3/webhooks/whk_existing', $request->getUri()->getPath());
        self::assertSame($payload, json_decode((string) $request->getBody(), true));
        self::assertSame('api-key-for-test', $request->getHeaderLine('access_token'));
    }

    public function testExistingPayloadContractRemainsTransparent(): void
    {
        $payload = $this->creationPayload() + ['authToken' => 'legacy-short-token'];
        $history = [];
        $service = $this->service(['id' => 'whk_legacy'], $history);

        $service->createNewWebhook([], [], [], $payload);

        self::assertSame($payload, json_decode((string) $history[0]['request']->getBody(), true));
    }

    /** @return array<string, mixed> */
    private function creationPayload(): array
    {
        return [
            'name' => 'CRM',
            'url' => 'https://crm.example.test/webhooks/asaas',
            'email' => 'webhooks@example.test',
            'enabled' => true,
            'interrupted' => false,
            'events' => ['PAYMENT_RECEIVED', 'PAYMENT_CONFIRMED'],
            'sendType' => 'SEQUENTIALLY',
        ];
    }

    /**
     * @param array<string, mixed> $response
     * @param list<array<string, mixed>> $history
     */
    private function service(array $response, array &$history): WebhookService
    {
        $stack = HandlerStack::create(new MockHandler([
            new Response(200, [], json_encode($response, JSON_THROW_ON_ERROR)),
        ]));
        $stack->push(Middleware::history($history));
        $guzzle = new GuzzleClient(['handler' => $stack, 'base_uri' => Environment::Sandbox->value]);

        return new WebhookService(new Client('api-key-for-test', Environment::Sandbox, 'WebhookTest/1.0', client: $guzzle));
    }
}
