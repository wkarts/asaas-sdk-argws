<?php

declare(strict_types=1);

namespace Asaas\Sdk\Tests\Unit;

use Asaas\Sdk\AsaasSdk;
use Asaas\Sdk\Config\AsaasConfig;
use Asaas\Sdk\Http\Environment;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;

final class FinancialReadsTest extends TestCase
{
    public function testFinancialFacadesSendDocumentedReadPathsAndFiltersWithoutBody(): void
    {
        $history = [];
        $cases = [
            ['financialTransaction', 'retrieveExtract', '/v3/financialTransactions', [], ['startDate' => '2026-10-01', 'finishDate' => '2026-10-31', 'order' => 'asc', 'offset' => 100, 'limit' => 100]],
            ['finance', 'retrieveAccountBalance', '/v3/finance/balance', [], []],
            ['finance', 'billingStatistics', '/v3/finance/payment/statistics', [], ['customer' => 'cus_test', 'anticipated' => false, 'dueDate[ge]' => '2026-10-01', 'dueDate[le]' => '2026-10-31']],
            ['finance', 'retrieveSplitValues', '/v3/finance/split/statistics', [], []],
            ['payment', 'listPayments', '/v3/payments', [], ['customer' => 'cus_test', 'externalReference' => 'FAT-123', 'status' => 'RECEIVED', 'paymentDate[ge]' => '2026-10-01', 'limit' => 100, 'offset' => 100]],
            ['paymentSplit', 'listPaidSplits', '/v3/payments/splits/paid', [], ['paymentId' => 'pay_test', 'creditDate[ge]' => '2026-10-01']],
            ['paymentSplit', 'listReceivedSplits', '/v3/payments/splits/received', [], ['status' => 'DONE', 'paymentConfirmedDate[le]' => '2026-10-31']],
            ['transfer', 'listTransfers', '/v3/transfers', [], ['dateCreated[ge]' => '2026-10-01', 'transferDate[le]' => '2026-10-31', 'type' => 'ASAAS_ACCOUNT']],
            ['anticipation', 'listAnticipations', '/v3/anticipations', [], ['payment' => 'pay_test', 'status' => 'CREDITED', 'limit' => 100]],
            ['bill', 'listBillPayments', '/v3/bill', [], ['status' => 'PAID', 'offset' => 100, 'limit' => 100]],
            ['installment', 'listInstallments', '/v3/installments', [], ['offset' => 0, 'limit' => 100]],
            ['subscription', 'listSubscriptions', '/v3/subscriptions', [], ['customer' => 'cus_test', 'includeDeleted' => true, 'externalReference' => 'CONTRACT-1']],
            ['pixTransaction', 'listTransactions', '/v3/pix/transactions', [], ['type' => 'CREDIT', 'endToEndIdentifier' => 'synthetic']],
            ['chargeback', 'listChargebacks', '/v3/chargebacks/', [], ['originDisputeDate[ge]' => '2026-10-01', 'status' => 'REQUESTED']],
            ['paymentRefund', 'retrieveRefundsOfASinglePayment', '/v3/payments/pay%2Ftest/refunds', ['id' => 'pay/test'], []],
            ['automaticPix', 'listAutomaticPixAuthorizations', '/v3/pix/automatic/authorizations', [], ['customerId' => 'cus_test', 'status' => 'ACTIVE', 'subscriptionId' => 'sub_test']],
            ['automaticPix', 'listAutomaticPixPaymentInstructions', '/v3/pix/automatic/paymentInstructions', [], ['authorizationId' => 'auth_test', 'customerId' => 'cus_test', 'paymentId' => 'pay_test', 'status' => 'REFUSED']],
            ['invoice', 'listInvoices', '/v3/invoices', [], ['effectiveDate[Ge]' => '2026-10-01', 'effectiveDate[Le]' => '2026-10-31', 'customer' => 'cus_test']],
            ['paymentDunning', 'listPaymentDunnings', '/v3/paymentDunnings', [], ['requestStartDate' => '2026-10-01', 'requestEndDate' => '2026-10-31', 'payment' => 'pay_test']],
            ['mobilePhoneRecharge', 'listMobileRecharges', '/v3/mobilePhoneRecharges', [], ['offset' => 100, 'limit' => 100]],
        ];
        $stack = HandlerStack::create(new MockHandler(array_fill(0, count($cases), new Response(200, [], '{"unknownFutureField":true}'))));
        $stack->push(Middleware::history($history));
        $sdk = new AsaasSdk(new AsaasConfig('synthetic', Environment::Sandbox, httpClient: new Client(['handler' => $stack, 'base_uri' => Environment::Sandbox->value])));

        foreach ($cases as $index => [$service, $method, $path, $pathParams, $query]) {
            self::assertSame(['unknownFutureField' => true], $sdk->{$service}->{$method}(pathParams: $pathParams, query: $query));
            $request = $history[$index]['request'];
            self::assertSame('GET', $request->getMethod());
            self::assertSame($path, $request->getUri()->getPath());
            self::assertSame('', (string) $request->getBody());
            $expected = array_map(static fn(mixed $value): string => is_bool($value) ? ($value ? 'true' : 'false') : (string) $value, $query);
            self::assertSame(http_build_query($expected, '', '&', PHP_QUERY_RFC3986), $request->getUri()->getQuery());
        }
    }

    public function testAutomaticPixInstructionFiltersExistInVersionedContract(): void
    {
        $schema = json_decode((string) file_get_contents(__DIR__ . '/../../resources/openapi.json'), true, 512, JSON_THROW_ON_ERROR);
        $operation = $schema['paths']['/v3/pix/automatic/paymentInstructions']['get'];
        $parameters = array_column($operation['parameters'], null, 'name');
        foreach (['authorizationId', 'customerId', 'paymentId', 'status'] as $filter) {
            self::assertSame('query', $parameters[$filter]['in']);
        }
        self::assertSame(['AWAITING_REQUEST', 'SCHEDULED', 'DONE', 'CANCELLED', 'REFUSED'], $parameters['status']['schema']['enum']);
        $billParameters = array_column($schema['paths']['/v3/bill']['get']['parameters'], null, 'name');
        self::assertContains('PAID', $billParameters['status']['schema']['enum']);
        foreach (['post', 'get'] as $method) {
            $properties = $schema['paths']['/v3/pix/automatic/authorizations'][$method]['responses']['200']['content']['application/json']['schema']['properties'];
            if ($method === 'get') {
                $properties = $properties['data']['items']['properties'];
            }
            self::assertSame(['MANUAL', 'SUBSCRIPTION'], $properties['paymentCreationMode']['enum']);
            self::assertContains('NOT_ALLOWED', $properties['retryPolicy']['enum']);
        }
    }
}
