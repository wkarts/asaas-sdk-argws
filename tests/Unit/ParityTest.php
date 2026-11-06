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
    public function testEveryGeneratedServiceHasAPublicFacade(): void
    {
        $types = [];
        foreach ((new \ReflectionClass(\Asaas\Sdk\AsaasSdk::class))->getProperties(\ReflectionProperty::IS_PUBLIC) as $property) {
            $type = $property->getType();
            self::assertInstanceOf(\ReflectionNamedType::class, $type);
            $types[] = $type->getName();
        }
        foreach (glob(__DIR__ . '/../../src/Service/Generated/*Service.php') ?: [] as $file) {
            $wrapper = 'Asaas\\Sdk\\Service\\' . basename($file, '.php');
            self::assertContains($wrapper, $types, 'Generated service missing from facade: ' . $wrapper);
        }
    }
}
