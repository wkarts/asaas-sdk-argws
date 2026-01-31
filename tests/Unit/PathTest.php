<?php

declare(strict_types=1);

namespace Asaas\Sdk\Tests\Unit;

use Asaas\Sdk\Exception\ValidationException;
use Asaas\Sdk\Util\Path;
use PHPUnit\Framework\TestCase;

final class PathTest extends TestCase
{
    public function testInterpolateReplacesParams(): void
    {
        $result = Path::interpolate('/payments/{id}', ['id' => 'pay_123']);

        self::assertSame('/payments/pay_123', $result);
    }

    public function testInterpolateThrowsOnMissingParam(): void
    {
        $this->expectException(ValidationException::class);

        Path::interpolate('/payments/{id}', []);
    }
}
