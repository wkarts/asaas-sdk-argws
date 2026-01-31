<?php

declare(strict_types=1);

namespace Asaas\Sdk\Tests\Unit;

use Asaas\Sdk\Util\Query;
use PHPUnit\Framework\TestCase;

final class QueryTest extends TestCase
{
    public function testNormalizeRemovesNullAndFormatsBool(): void
    {
        $input = [
            'limit' => 10,
            'active' => true,
            'inactive' => false,
            'empty' => null,
            'dateCreated[ge]' => '2024-01-01',
        ];

        $expected = [
            'limit' => 10,
            'active' => 'true',
            'inactive' => 'false',
            'dateCreated[ge]' => '2024-01-01',
        ];

        self::assertSame($expected, Query::normalize($input));
    }
}
