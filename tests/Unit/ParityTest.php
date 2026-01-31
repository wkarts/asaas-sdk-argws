<?php

declare(strict_types=1);

namespace Asaas\Sdk\Tests\Unit;

use Asaas\Sdk\Generator\ParityVerifier;
use PHPUnit\Framework\TestCase;

final class ParityTest extends TestCase
{
    public function testFacadeHasExpectedServices(): void
    {
        $verifier = new ParityVerifier();
        $expected = $verifier->expectedServices();
        $actual = $verifier->currentServices();

        sort($expected);

        self::assertSame($expected, $actual);
    }
}
