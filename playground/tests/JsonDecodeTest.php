<?php

declare(strict_types=1);

namespace Playground\Tests;

use PHPUnit\Framework\TestCase;
use Playground\Utils\Json;

final class JsonDecodeTest extends TestCase
{
    public function testDecodeInvalidJsonReturnsError(): void
    {
        $result = Json::decode('{invalid');

        $this->assertNull($result['data']);
        $this->assertNotNull($result['error']);
    }
}
