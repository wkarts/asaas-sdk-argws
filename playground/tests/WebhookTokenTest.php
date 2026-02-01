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
}
