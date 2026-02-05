<?php

declare(strict_types=1);

namespace Asaas\Sdk\Config;

use Asaas\Sdk\Http\Environment;
use GuzzleHttp\Client;
use Psr\Log\LoggerInterface;

final class AsaasConfig
{
    public function __construct(
        public string $apiKey,
        public Environment $environment = Environment::Production,
        public string $appName = 'AsaasSdk/1.0',
        public float $timeout = 30.0,
        public float $connectTimeout = 10.0,
        public ?LoggerInterface $logger = null,
        public ?Client $httpClient = null
    ) {}
}
