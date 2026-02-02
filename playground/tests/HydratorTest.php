<?php

declare(strict_types=1);

namespace Playground\Tests;

use PHPUnit\Framework\TestCase;
use Playground\Utils\ArgumentHydrator;

final class HydratorTest extends TestCase
{
    public function testHydrateNestedObject(): void
    {
        $hydrator = new ArgumentHydrator();
        $payload = [
            'name' => 'Cliente',
            'address' => [
                'street' => 'Rua A',
                'number' => 123,
            ],
        ];

        $customer = $hydrator->hydrate(TestCustomer::class, $payload);

        $this->assertSame('Cliente', $customer->name);
        $this->assertInstanceOf(TestAddress::class, $customer->address);
        $this->assertSame('Rua A', $customer->address->street);
        $this->assertSame(123, $customer->address->number);
    }
}

final class TestCustomer
{
    public string $name;
    public TestAddress $address;
}

final class TestAddress
{
    public string $street;
    public int $number;
}
