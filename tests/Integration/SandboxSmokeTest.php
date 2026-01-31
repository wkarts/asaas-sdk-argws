<?php

declare(strict_types=1);

namespace Asaas\Sdk\Tests\Integration;

use Asaas\Sdk\AsaasSdk;
use Asaas\Sdk\Config\AsaasConfig;
use Asaas\Sdk\Http\Environment;
use PHPUnit\Framework\TestCase;

final class SandboxSmokeTest extends TestCase
{
    public function testListPaymentsWhenCredentialsProvided(): void
    {
        $apiKey = getenv('ASAAS_API_KEY');
        if ($apiKey === false || $apiKey === '') {
            self::markTestSkipped('ASAAS_API_KEY não definido.');
        }

        $env = getenv('ASAAS_ENV') === 'production' ? Environment::Production : Environment::Sandbox;
        $appName = getenv('ASAAS_APP_NAME') ?: 'AsaasSdk/1.0';

        $sdk = new AsaasSdk(new AsaasConfig($apiKey, $env, $appName));

        $result = $sdk->payment->listPayments(['limit' => 1]);

        self::assertIsArray($result);
    }
}
