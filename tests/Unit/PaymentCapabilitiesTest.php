<?php

declare(strict_types=1);

namespace Asaas\Sdk\Tests\Unit;

use Asaas\Sdk\AsaasSdk;
use Asaas\Sdk\Config\AsaasConfig;
use Asaas\Sdk\Exception\ApiException;
use Asaas\Sdk\Exception\TransportException;
use Asaas\Sdk\Http\Environment;
use Asaas\Sdk\Http\RetryMiddleware;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;

final class PaymentCapabilitiesTest extends TestCase
{
    public function testAutomaticPixLifecycleIsAvailableThroughFacade(): void
    {
        $history = [];
        $sdk = $this->sdk(array_fill(0, 7, new Response(200, [], '{"id":"synthetic","status":"CREATED"}')), $history);
        $payload = ['customerId' => 'cus_test', 'frequency' => 'MONTHLY', 'contractId' => 'test', 'startDate' => '2027-01-15', 'value' => 10, 'immediateQrCode' => ['expirationSeconds' => 3600, 'originalValue' => 10], 'paymentCreationMode' => 'SUBSCRIPTION', 'retryPolicy' => 'ALLOW_THREE_IN_SEVEN_DAYS'];
        $result = $sdk->automaticPix->createAnAutomaticPixAuthorization(payload: $payload);
        self::assertSame('CREATED', $result['status']);
        $sdk->automaticPix->listAutomaticPixAuthorizations(query: ['offset' => 20, 'limit' => 10, 'subscriptionId' => 'sub_test']);
        $sdk->automaticPix->retrieveASingleAutomaticPixAuthorization(['id' => 'auth/test']);
        $sdk->automaticPix->cancelAnAutomaticPixAuthorization(['id' => 'auth_test']);
        $sdk->automaticPix->retrieveASingleAutomaticPixPaymentInstruction(['id' => 'instruction_test']);
        $sdk->automaticPix->listAutomaticPixPaymentInstructions(query: ['authorizationId' => 'auth_test', 'status' => 'REFUSED']);
        $sdk->automaticPix->createAnAutomaticPixPaymentInstructionRetry(['id' => 'instruction_test'], payload: ['dueDate' => '2027-01-17']);
        $paths = [
            ['POST', '/v3/pix/automatic/authorizations'],
            ['GET', '/v3/pix/automatic/authorizations'],
            ['GET', '/v3/pix/automatic/authorizations/auth%2Ftest'],
            ['DELETE', '/v3/pix/automatic/authorizations/auth_test'],
            ['GET', '/v3/pix/automatic/paymentInstructions/instruction_test'],
            ['GET', '/v3/pix/automatic/paymentInstructions'],
            ['POST', '/v3/pix/automatic/paymentInstructions/instruction_test/retries'],
        ];
        foreach ($paths as $i => [$method, $path]) {
            self::assertSame($method, $history[$i]['request']->getMethod());
            self::assertSame($path, $history[$i]['request']->getUri()->getPath());
            if ($method === 'GET') {
                self::assertSame('', (string) $history[$i]['request']->getBody());
            }
        }
        self::assertSame($payload, json_decode((string) $history[0]['request']->getBody(), true));
        self::assertSame('offset=20&limit=10&subscriptionId=sub_test', $history[1]['request']->getUri()->getQuery());
        self::assertSame('authorizationId=auth_test&status=REFUSED', $history[5]['request']->getUri()->getQuery());
        self::assertSame(['dueDate' => '2027-01-17'], json_decode((string) $history[6]['request']->getBody(), true));
    }

    public function testHostedCardPaymentPreservesInvoiceAndDoesNotSendCardData(): void
    {
        foreach (['CREDIT_CARD', 'UNDEFINED'] as $billingType) {
            $history = [];
            $sdk = $this->sdk([new Response(200, [], '{"id":"pay_test","invoiceUrl":"https://example.invalid/invoice"}')], $history);
            $payload = ['customer' => 'cus_test', 'value' => 10, 'dueDate' => '2027-01-15', 'billingType' => $billingType];
            $result = $sdk->payment->createHostedCardPayment($payload);
            self::assertSame('https://example.invalid/invoice', $result['invoiceUrl']);
            self::assertSame($payload, json_decode((string) $history[0]['request']->getBody(), true));
            self::assertSame('/v3/payments', $history[0]['request']->getUri()->getPath());
        }
        $history = [];
        $sdk = $this->sdk([new Response(200, [], '{}')], $history);
        $sdk->payment->createHostedCardPayment(['customer' => 'cus_test']);
        self::assertSame('CREDIT_CARD', json_decode((string) $history[0]['request']->getBody(), true)['billingType']);
    }

    public function testHostedCardPaymentRejectsDirectCardDataAndUnsupportedTypesBeforeHttp(): void
    {
        $invalid = [['billingType' => 'DEBIT_CARD'], ['billingType' => 'PIX'], ['billingType' => 'VOUCHER']];
        foreach (['creditCard', 'creditCardToken', 'creditCardHolderInfo', 'card', 'cardToken', 'cardHolderInfo', 'cardType'] as $field) {
            $invalid[] = [$field => null];
        }
        foreach ($invalid as $payload) {
            $history = [];
            $sdk = $this->sdk([], $history);
            try {
                $sdk->payment->createHostedCardPayment($payload);
                self::fail('Invalid hosted payload must fail locally');
            } catch (\InvalidArgumentException $exception) {
                self::assertSame([], $history);
            }
        }
    }

    public function testWritesAreNotReplayedEvenWithIdempotencyHeader(): void
    {
        foreach (['POST', 'PUT', 'PATCH', 'DELETE'] as $method) {
            foreach ([429, 500, 503, 'timeout'] as $failure) {
                $history = [];
                $first = $failure === 'timeout' ? new ConnectException('synthetic', new Request($method, '/v3/test')) : new Response($failure, [], '{}');
                $stack = HandlerStack::create(new MockHandler([$first, new Response(200, [], '{}')]));
                $stack->push(RetryMiddleware::create(3, 0));
                $stack->push(Middleware::history($history));
                $client = new \Asaas\Sdk\Http\Client('synthetic', Environment::Sandbox, 'test', client: new Client(['handler' => $stack, 'base_uri' => Environment::Sandbox->value]));
                try {
                    $client->request($method, '/v3/test', headers: ['Idempotency-Key' => 'synthetic']);
                    self::fail('Failure must be surfaced');
                } catch (ApiException|TransportException $exception) {
                    self::assertCount(1, $history);
                }
            }
        }
    }

    public function testSubaccountOperationsUseVerifiedRoutesAndEmptyBodies(): void
    {
        $history = [];
        $sdk = $this->sdk([new Response(204), new Response(200, [], '{"deleted":true}')], $history);
        self::assertSame([], $sdk->subaccount->resendNonBaasSubaccountActivationLink(['id' => 'acc_test']));
        self::assertSame(['deleted' => true], $sdk->subaccount->closeNonBaasSubaccount(['id' => 'acc_test']));
        self::assertSame('POST', $history[0]['request']->getMethod());
        self::assertSame('/v3/accounts/acc_test/resendActivationLink', $history[0]['request']->getUri()->getPath());
        self::assertSame('DELETE', $history[1]['request']->getMethod());
        self::assertSame('/v3/accounts/acc_test', $history[1]['request']->getUri()->getPath());
        foreach ($history as $transaction) {
            self::assertSame('', (string) $transaction['request']->getBody());
        }
    }

    public function testReadRetriesStopAtLimitAndClientErrorsAreNotRetried(): void
    {
        foreach ([400 => 1, 401 => 1, 404 => 1, 429 => 4, 503 => 4] as $status => $attempts) {
            $history = [];
            $sdk = $this->sdk(array_fill(0, 5, new Response($status, [], '{}')), $history);
            try {
                $sdk->automaticPix->listAutomaticPixAuthorizations();
                self::fail('Error must be surfaced');
            } catch (ApiException $exception) {
                self::assertSame($status, $exception->getStatusCode());
                self::assertCount($attempts, $history);
            }
        }
    }

    /**
     * @param list<\Psr\Http\Message\ResponseInterface|\Throwable> $responses
     * @param list<array<string, mixed>> $history
     */
    private function sdk(array $responses, array &$history): AsaasSdk
    {
        $stack = HandlerStack::create(new MockHandler($responses));
        $stack->push(RetryMiddleware::create(3, 0));
        $stack->push(Middleware::history($history));
        return new AsaasSdk(new AsaasConfig('synthetic', Environment::Sandbox, httpClient: new Client(['handler' => $stack, 'base_uri' => Environment::Sandbox->value])));
    }
}
