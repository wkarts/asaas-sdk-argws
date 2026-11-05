<?php

declare(strict_types=1);

namespace Asaas\Sdk\Tests\Unit;

use Asaas\Sdk\Webhook\WebhookToken;
use PHPUnit\Framework\TestCase;

final class WebhookTokenTest extends TestCase
{
    public function testGenerateProducesDistinctValidTokens(): void
    {
        $first = WebhookToken::generate();
        $second = WebhookToken::generate();

        self::assertMatchesRegularExpression('/\A[a-f0-9]{64}\z/', $first);
        self::assertNotSame($first, $second);
        self::assertTrue(WebhookToken::isValid($first));
        self::assertTrue(WebhookToken::isValid($second));
    }

    public function testValidatesNewTokenBoundariesAndRejectsUnsafeValues(): void
    {
        self::assertTrue(WebhookToken::isValid(str_repeat('a', 32)));
        self::assertTrue(WebhookToken::isValid(str_repeat('b', 255)));
        self::assertTrue(WebhookToken::isValid('whsec_' . str_repeat('Ab9-_', 10)));

        foreach ([
            null,
            false,
            123,
            [],
            '',
            str_repeat('a', 31),
            str_repeat('a', 256),
            str_repeat('a', 32) . ' ',
            str_repeat('a', 32) . "\t",
            str_repeat('a', 32) . "\n",
            str_repeat('a', 32) . "\0",
            str_repeat('a', 32) . "\u{00A0}",
            str_repeat('a', 32) . "\u{200B}",
            str_repeat('a', 32) . "\xFF",
            '$aact_' . str_repeat('a', 40),
            '$AACT_' . str_repeat('a', 40),
        ] as $invalid) {
            self::assertFalse(WebhookToken::isValid($invalid));
        }
    }

    public function testMatchesLegacyTokensWithoutAcceptingEmptyOrMalformedCredentials(): void
    {
        self::assertTrue(WebhookToken::matches('legacy-token', 'legacy-token'));
        self::assertFalse(WebhookToken::matches('legacy-token', 'Legacy-token'));
        self::assertFalse(WebhookToken::matches('legacy-token', 'legacy-token '));
        self::assertFalse(WebhookToken::matches('legacy-token', null));
        self::assertFalse(WebhookToken::matches('legacy-token', ['legacy-token']));
        self::assertFalse(WebhookToken::matches('', ''));
        self::assertFalse(WebhookToken::matches(null, ''));
        self::assertFalse(WebhookToken::matches(123, '123'));
    }

    public function testHeaderRedactionPreservesUnrelatedHeadersAndArrayValues(): void
    {
        $headers = [
            'Asaas-Access-Token' => 'secret-value',
            'ASAAS-ACCESS-TOKEN' => ['first-secret', 'second-secret'],
            'Content-Type' => 'application/json',
            'X-Trace' => ['trace-a', 'trace-b'],
        ];

        $redacted = WebhookToken::redactHeaders($headers);

        self::assertSame('[REDACTED]', $redacted['Asaas-Access-Token']);
        self::assertSame(['[REDACTED]', '[REDACTED]'], $redacted['ASAAS-ACCESS-TOKEN']);
        self::assertSame('application/json', $redacted['Content-Type']);
        self::assertSame(['trace-a', 'trace-b'], $redacted['X-Trace']);
        self::assertSame('secret-value', $headers['Asaas-Access-Token']);
    }
}
