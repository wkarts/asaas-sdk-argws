<?php

declare(strict_types=1);

namespace Playground\Tests;

use PHPUnit\Framework\TestCase;
use Playground\Utils\Mask;

final class MaskingTest extends TestCase
{
    public function testMaskingSecrets(): void
    {
        $masked = Mask::maskArray([
            'api_key' => '1234567890',
            'token' => 'abcdef',
            'name' => 'Cliente',
        ]);

        $this->assertSame('123****890', $masked['api_key']);
        $this->assertSame('******', $masked['token']);
        $this->assertSame('Cliente', $masked['name']);
    }
}
