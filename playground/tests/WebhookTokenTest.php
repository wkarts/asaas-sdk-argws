<?php

declare(strict_types=1);

namespace Playground\Tests;

use PHPUnit\Framework\TestCase;
use Playground\Controllers\WebhookController;

final class WebhookTokenTest extends TestCase
{
    public function testTokenMismatchReturns401(): void
    {
        $result = WebhookController::tokenStatus('expected', 'invalid');

        $this->assertFalse($result['authorized']);
        $this->assertSame(401, $result['status']);
    }

    public function testLegacyTokenStillAuthenticatesWhenItMatches(): void
    {
        $result = WebhookController::tokenStatus('legacy-short-token', 'legacy-short-token');

        self::assertTrue($result['authorized']);
        self::assertTrue($result['validated']);
        self::assertSame(200, $result['status']);
    }

    public function testMissingTokenIsUnauthorizedWhenConfigured(): void
    {
        $result = WebhookController::tokenStatus('configured-token', null);

        self::assertFalse($result['authorized']);
        self::assertFalse($result['validated']);
        self::assertSame(401, $result['status']);
    }

    public function testEmptyConfigurationKeepsExplicitLegacyUnvalidatedStatus(): void
    {
        $result = WebhookController::tokenStatus('', null);

        self::assertTrue($result['authorized']);
        self::assertFalse($result['validated']);
        self::assertSame(200, $result['status']);
    }
}
