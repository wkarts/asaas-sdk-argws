<?php

declare(strict_types=1);

namespace Asaas\Sdk\Tests\Unit;

use Asaas\Sdk\Http\Client;
use Asaas\Sdk\Http\Environment;
use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;

final class ClientTest extends TestCase
{
    public function testRequestAddsHeadersAndParsesJson(): void
    {
        $history = [];
        $historyMiddleware = Middleware::history($history);

        $mock = new MockHandler([
            new Response(200, [], json_encode(['status' => 'ok'])),
        ]);

        $stack = HandlerStack::create($mock);
        $stack->push($historyMiddleware);

        $guzzle = new GuzzleClient(['handler' => $stack, 'base_uri' => Environment::Sandbox->value]);

        $client = new Client('token', Environment::Sandbox, 'MinhaApp/1.0', client: $guzzle);

        $result = $client->request('GET', '/payments');

        self::assertSame(['status' => 'ok'], $result);
        self::assertCount(1, $history);

        $request = $history[0]['request'];
        self::assertSame('application/json', $request->getHeaderLine('Accept'));
        self::assertSame('MinhaApp/1.0', $request->getHeaderLine('User-Agent'));
        self::assertSame('token', $request->getHeaderLine('access_token'));
    }
}
