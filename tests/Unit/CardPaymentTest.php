<?php

declare(strict_types=1);

namespace Asaas\Sdk\Tests\Unit;

use Asaas\Sdk\Http\Client;
use Asaas\Sdk\Http\Environment;
use Asaas\Sdk\Http\RetryMiddleware;
use Asaas\Sdk\Service\PaymentService;
use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Exception\ConnectException;
use PHPUnit\Framework\TestCase;

final class CardPaymentTest extends TestCase
{
    public function testCardContractsRemainDistinct(): void
    {
        $history = [];
        $service = $this->service([new Response(200, [], '{"id":"pay_test"}'), new Response(200, [], '{"id":"pay_test"}')], $history);
        $new = ['cardType' => 'VOUCHER', 'cardToken' => 'synthetic-token'];
        $old = ['creditCardToken' => 'synthetic-token'];
        $service->payAChargeWithCard(['id' => 'pay_test'], payload: $new);
        $service->payAChargeWithCreditCard(['id' => 'pay_test'], payload: $old);
        self::assertSame('/v3/payments/pay_test/payWithCard', $history[0]['request']->getUri()->getPath());
        self::assertSame('/v3/payments/pay_test/payWithCreditCard', $history[1]['request']->getUri()->getPath());
        self::assertSame($new, json_decode((string) $history[0]['request']->getBody(), true));
        self::assertSame($old, json_decode((string) $history[1]['request']->getBody(), true));
    }

    public function testCardPaymentsNeverRetryOnRateLimitServerErrorOrTimeout(): void
    {
        foreach (['payWithCard', 'payWithCreditCard'] as $endpoint) {
            foreach ([400, 401, 429, 500, 503, 'timeout'] as $failure) {
                $history = [];
                $first = $failure === 'timeout' ? new ConnectException('synthetic timeout', new Request('POST', '/v3/payments/pay_test/'.$endpoint)) : new Response($failure, [], '{}');
                $service = $this->service([$first, new Response(200, [], '{"id":"unexpected_retry"}')], $history);
                try {
                    if ($endpoint === 'payWithCard') {
                        $service->payAChargeWithCard(['id' => 'pay_test'], payload: ['cardType' => 'CREDIT', 'cardToken' => 'synthetic-token']);
                    } else {
                        $service->payAChargeWithCreditCard(['id' => 'pay_test'], payload: ['creditCardToken' => 'synthetic-token']);
                    }
                    self::fail('Failure must remain visible to the caller');
                } catch (\Asaas\Sdk\Exception\ApiException|\Asaas\Sdk\Exception\TransportException $exception) {
                    self::assertCount(1, $history, $endpoint.' / '.$failure);
                }
            }
        }
    }

    public function testCardCreationDoesNotRetryButGetKeepsReadRetry(): void
    {
        $history = [];
        $service = $this->service([new Response(503, [], '{}'), new Response(200, [], '{}')], $history);
        try {
            $service->createNewPaymentWithCreditCard(payload: ['creditCardToken' => 'synthetic-token']);
            self::fail('Card creation must not retry');
        } catch (\Asaas\Sdk\Exception\ApiException $exception) {
            self::assertCount(1, $history);
        }
        $history = [];
        $service = $this->service([new Response(503, [], '{}'), new Response(200, [], '{"id":"pay_test"}')], $history);
        $service->retrieveASinglePayment(['id' => 'pay_test']);
        self::assertCount(2, $history);
    }

    private function service(array $responses, array &$history): PaymentService
    {
        $stack = HandlerStack::create(new MockHandler($responses));
        $stack->push(RetryMiddleware::create(3, 0));
        $stack->push(Middleware::history($history));
        $guzzle = new GuzzleClient(['handler' => $stack, 'base_uri' => Environment::Sandbox->value]);
        return new PaymentService(new Client('synthetic-api-key', Environment::Sandbox, 'CardContractTest', client: $guzzle));
    }
}
