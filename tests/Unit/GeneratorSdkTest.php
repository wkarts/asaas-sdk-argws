<?php

declare(strict_types=1);

namespace Asaas\Sdk\Tests\Unit;

use Asaas\Sdk\Generator\SdkGenerator;
use PHPUnit\Framework\TestCase;

final class GeneratorSdkTest extends TestCase
{
    public function testGenerateCreatesServiceFile(): void
    {
        $openapi = [
            'paths' => [
                '/payments' => [
                    'get' => [
                        'operationId' => 'listPayments',
                        'tags' => ['Payment'],
                        'responses' => [
                            '200' => [
                                'content' => [
                                    'application/json' => [],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
            'components' => [
                'schemas' => [],
            ],
        ];

        $tempDir = sys_get_temp_dir() . '/asaas-sdk-test-' . uniqid();
        mkdir($tempDir . '/services', 0777, true);
        mkdir($tempDir . '/models', 0777, true);
        mkdir($tempDir . '/templates', 0777, true);

        file_put_contents($tempDir . '/templates/service.php.tpl', "<?php\n\nnamespace Asaas\\\\Sdk\\\\Service\\\\Generated;\n\nfinal class {{className}} {\n{{methods}}\n}\n");
        file_put_contents($tempDir . '/templates/dto.php.tpl', '');
        file_put_contents($tempDir . '/templates/enum.php.tpl', '');

        $generator = new SdkGenerator();
        $generator->generate($openapi, ['Payment' => 'PaymentService'], $tempDir . '/services', $tempDir . '/models', $tempDir . '/templates');

        self::assertFileExists($tempDir . '/services/PaymentService.php');
    }
}
