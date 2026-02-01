<?php

declare(strict_types=1);

namespace Playground;

use Asaas\Sdk\AsaasSdk;
use Asaas\Sdk\Config\AsaasConfig;
use Asaas\Sdk\Http\Client;
use Asaas\Sdk\Http\Environment;
use Dotenv\Dotenv;
use Playground\Storage\Migrate;
use Playground\Storage\Sqlite;
use Slim\Factory\AppFactory;
use Slim\App;

final class Bootstrap
{
    private string $basePath;
    private ?AsaasSdk $sdk = null;
    private ?Client $client = null;

    public function __construct(string $basePath)
    {
        $this->basePath = $basePath;
        $this->loadEnv();
        Migrate::run($this->basePath);
    }

    public function app(): App
    {
        $app = AppFactory::create();
        $app->addRoutingMiddleware();
        $app->addErrorMiddleware(true, true, true);

        Routes::register($app, $this);

        return $app;
    }

    public function sdk(): AsaasSdk
    {
        if ($this->sdk instanceof AsaasSdk) {
            return $this->sdk;
        }

        $config = new AsaasConfig(
            $this->env('ASAAS_API_KEY', ''),
            $this->environment(),
            $this->env('ASAAS_APP_NAME', 'Asaas Playground'),
            (float) $this->env('ASAAS_TIMEOUT', '30'),
            (float) $this->env('ASAAS_CONNECT_TIMEOUT', '10')
        );

        $this->sdk = new AsaasSdk($config);
        $this->client = new Client(
            $config->apiKey,
            $config->environment,
            $config->appName,
            $config->timeout,
            $config->connectTimeout
        );

        return $this->sdk;
    }

    public function client(): Client
    {
        if ($this->client instanceof Client) {
            return $this->client;
        }

        $this->sdk();

        return $this->client;
    }

    public function db(): Sqlite
    {
        return new Sqlite($this->basePath . '/storage/database.sqlite');
    }

    public function basePath(): string
    {
        return $this->basePath;
    }

    public function env(string $key, string $default = ''): string
    {
        $value = getenv($key);
        if ($value === false || $value === '') {
            return $default;
        }

        return $value;
    }

    public function environment(): Environment
    {
        $env = strtolower($this->env('ASAAS_ENV', 'sandbox'));

        return $env === 'production' ? Environment::Production : Environment::Sandbox;
    }

    private function loadEnv(): void
    {
        $envPath = $this->basePath . '/.env';
        if (is_file($envPath)) {
            Dotenv::createImmutable($this->basePath)->safeLoad();
        }
    }
}
