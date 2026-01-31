<?php

declare(strict_types=1);

namespace Asaas\Sdk\Tests\Unit;

use Asaas\Sdk\Exception\ApiException;
use PHPUnit\Framework\TestCase;

final class ApiExceptionTest extends TestCase
{
    public function testParseErrorsFromResponse(): void
    {
        $body = json_encode([
            'errors' => [
                ['code' => 'invalid', 'description' => 'Falha no campo'],
            ],
        ]);

        $exception = ApiException::fromResponse(400, (string) $body);

        self::assertSame(400, $exception->getStatusCode());
        self::assertSame('Falha no campo', $exception->getMessage());
        self::assertCount(1, $exception->getErrors());
    }
}
