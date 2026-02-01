<?php

declare(strict_types=1);

namespace Asaas\Sdk\Tests\Unit;

use Asaas\Sdk\Generator\OpenApiBuilder;
use PHPUnit\Framework\TestCase;

final class GeneratorOpenApiTest extends TestCase
{
    public function testExtractOpenApiJsonFromMarkdown(): void
    {
        $markdown = <<<MD
            # Exemplo

            OpenAPI definition
            ```json
            {"openapi":"3.0.0","paths":{"/payments":{}}}
            ```
            MD;

        $builder = new OpenApiBuilder();
        $result = $builder->extractOpenApiJson($markdown);

        self::assertIsArray($result);
        self::assertArrayHasKey('paths', $result);
    }
}
