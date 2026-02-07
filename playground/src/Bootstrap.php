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
use Psr\Http\Message\ServerRequestInterface;
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
        $app->addBodyParsingMiddleware();
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

        $config = $this->configFor(null, null);

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

    public function sdkForRequest(ServerRequestInterface $request, ?string $apiKeyOverride = null): AsaasSdk
    {
        $config = $this->configFor(
            $apiKeyOverride,
            $this->extractEnvHeader($request)
        );

        return new AsaasSdk($config);
    }

    public function clientForRequest(ServerRequestInterface $request, ?string $apiKeyOverride = null): Client
    {
        $config = $this->configFor(
            $apiKeyOverride,
            $this->extractEnvHeader($request)
        );

        return new Client(
            $config->apiKey,
            $config->environment,
            $config->appName,
            $config->timeout,
            $config->connectTimeout
        );
    }

    public function db(): Sqlite
    {
        return new Sqlite($this->basePath . '/storage/database.sqlite');
    }

    public function basePath(): string
    {
        return $this->basePath;
    }

    /**
     * Retorna a versão instalada da SDK (argws/asaas-sdk-php), quando disponível.
     * Usado para exibição no Playground/Swagger e para diagnóstico.
     */
    public function sdkVersion(): string
    {
        // Composer 2 expõe InstalledVersions, sem dependências extras.
        if (class_exists('Composer\\InstalledVersions')) {
            try {
                /** @var class-string $c */
                $c = 'Composer\\InstalledVersions';
                $pretty = $c::getPrettyVersion('argws/asaas-sdk-php');
                if (is_string($pretty) && $pretty !== '') {
                    return $pretty;
                }
            } catch (\Throwable) {
                // fallback abaixo
            }
        }

        // Fallback: variável de ambiente (útil em Docker/build) ou "dev".
        return $this->env('ASAAS_SDK_VERSION', 'dev');
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

    public function environmentFromString(?string $env): Environment
    {
        $env = strtolower((string) $env);

        return $env === 'production' ? Environment::Production : Environment::Sandbox;
    }

    private function loadEnv(): void
    {
        $envPath = $this->basePath . '/.env';
        if (is_file($envPath)) {
            Dotenv::createImmutable($this->basePath)->safeLoad();
        }
    }

    private function extractEnvHeader(ServerRequestInterface $request): ?string
    {
        $header = $request->getHeaderLine('X-Asaas-Env');

        return $header !== '' ? $header : null;
    }

    private function configFor(?string $apiKeyOverride, ?string $envOverride): AsaasConfig
    {
        $apiKey = $apiKeyOverride ?? $this->env('ASAAS_API_KEY', '');
        $environment = $envOverride ? $this->environmentFromString($envOverride) : $this->environment();

        return new AsaasConfig(
            $apiKey,
            $environment,
            $this->env('ASAAS_APP_NAME', 'Asaas Playground'),
            (float) $this->env('ASAAS_TIMEOUT', '30'),
            (float) $this->env('ASAAS_CONNECT_TIMEOUT', '10')
        );
    }
}
