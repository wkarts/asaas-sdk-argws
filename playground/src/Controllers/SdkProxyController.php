<?php

declare(strict_types=1);

namespace Playground\Controllers;

use Asaas\Sdk\AsaasSdk;
use Playground\Utils\ReflectionScanner;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final class SdkProxyController extends AbstractController
{
    public function catalog(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $scanner = new ReflectionScanner($this->bootstrap->basePath());
        $catalog = $scanner->catalog();
        $services = array_values(array_filter(
            $catalog['services'],
            fn(string $class): bool => !str_contains($class, '\\Generated\\')
        ));
        $methods = array_intersect_key($catalog['methods'], array_flip($services));

        return $this->json([
            'services' => $services,
            'methods' => $methods,
        ]);
    }

    /**
     * @param array<string, string> $args
     */
    public function call(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $serviceName = (string) ($args['service'] ?? '');
        $methodName = (string) ($args['method'] ?? '');
        $payload = (array) $request->getParsedBody();
        $meta = is_array($payload['meta'] ?? null) ? (array) $payload['meta'] : null;
        $apiKey = $this->extractApiKey($request, $meta);

        $start = microtime(true);
        $success = false;
        $responseData = null;
        $errorMessage = null;

        try {
            if ($serviceName === '' || $methodName === '') {
                throw new \InvalidArgumentException('Informe service e method na URL.');
            }

            $serviceClass = $this->resolveServiceClass($serviceName);
            if ($serviceClass === null || !class_exists($serviceClass)) {
                throw new \RuntimeException('Service inválido.');
            }

            $sdk = $this->bootstrap->sdkForRequest($request, $apiKey);
            $instance = $this->resolveService($serviceClass, $sdk, $request, $apiKey);

            if (!method_exists($serviceClass, $methodName)) {
                throw new \RuntimeException('Método inválido.');
            }

            $reflection = new \ReflectionMethod($serviceClass, $methodName);
            if (!$reflection->isPublic() || $reflection->isConstructor() || $reflection->isDestructor()) {
                throw new \RuntimeException('Método inválido.');
            }

            $callArgs = $this->normalizeCallArgs($payload['args'] ?? []);
            $responseData = $reflection->invokeArgs($instance, $callArgs);
            $success = true;
        } catch (\Throwable $exception) {
            $errorMessage = $exception->getMessage();
        }

        $duration = (int) ((microtime(true) - $start) * 1000);

        return $this->json([
            'success' => $success,
            'duration_ms' => $duration,
            'response' => $success ? $responseData : null,
            'error' => $success ? null : $errorMessage,
        ], $success ? 200 : 500);
    }

    public function openapi(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        return $this->json($this->openApiSpec($request));
    }

    public function swagger(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        return $this->render('swagger');
    }

    public function scalar(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        return $this->render('scalar');
    }

    public function postmanCollection(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        return $this->json($this->postmanCollectionSpec($request));
    }

    public function postmanEnv(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $env = (string) ($args['env'] ?? '');

        return $this->json($this->postmanEnvSpec($request, $env));
    }

    private function resolveServiceClass(string $service): ?string
    {
        if (str_starts_with($service, 'Asaas\\Sdk\\Service\\')) {
            return $service;
        }

        $normalized = str_replace(['-', '_'], ' ', $service);
        $normalized = str_replace(' ', '', ucwords($normalized));

        if (!str_ends_with($normalized, 'Service')) {
            $normalized .= 'Service';
        }

        return 'Asaas\\Sdk\\Service\\' . $normalized;
    }

    private function resolveService(string $class, AsaasSdk $sdk, ServerRequestInterface $request, ?string $apiKey): object
    {
        $short = (new \ReflectionClass($class))->getShortName();
        $property = lcfirst(str_replace('Service', '', $short));

        if (property_exists($sdk, $property)) {
            return $sdk->{$property};
        }

        $constructor = (new \ReflectionClass($class))->getConstructor();
        if ($constructor !== null && $constructor->getNumberOfParameters() > 0) {
            return new $class($this->bootstrap->clientForRequest($request, $apiKey));
        }

        return new $class();
    }

    /**
     * @param mixed $args
     * @return array<int, mixed>
     */
    private function normalizeCallArgs(mixed $args): array
    {
        $args = is_array($args) ? array_values($args) : [];
        $args = array_pad($args, 4, null);

        $pathParams = is_array($args[0]) ? $args[0] : [];
        $query = is_array($args[1]) ? $args[1] : [];
        $headers = is_array($args[2]) ? $args[2] : [];
        $payload = is_array($args[3]) ? $args[3] : null;

        return [$pathParams, $query, $headers, $payload];
    }

    /**
     * @return array<string, mixed>
     */
    private function openApiSpec(ServerRequestInterface $request): array
    {
        $serverUrl = $this->buildServerUrl($request);

        return [
            'openapi' => '3.0.3',
            'info' => [
                'title' => 'Asaas SDK Proxy',
                'version' => '1.0.0',
                'description' => 'Proxy HTTP para executar métodos da SDK Asaas via REST.',
            ],
            'servers' => [
                ['url' => $serverUrl],
            ],
            'paths' => [
                '/api/sdk/catalog' => [
                    'get' => [
                        'summary' => 'Lista services e métodos disponíveis',
                        'responses' => [
                            '200' => [
                                'description' => 'Catálogo da SDK',
                                'content' => [
                                    'application/json' => [
                                        'schema' => [
                                            'type' => 'object',
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
                '/api/sdk/call/{service}/{method}' => [
                    'post' => [
                        'summary' => 'Executa um método da SDK',
                        'parameters' => [
                            [
                                'name' => 'service',
                                'in' => 'path',
                                'required' => true,
                                'schema' => ['type' => 'string'],
                            ],
                            [
                                'name' => 'method',
                                'in' => 'path',
                                'required' => true,
                                'schema' => ['type' => 'string'],
                            ],
                        ],
                        'requestBody' => [
                            'required' => false,
                            'content' => [
                                'application/json' => [
                                    'schema' => [
                                        '$ref' => '#/components/schemas/ProxyCallRequest',
                                    ],
                                ],
                            ],
                        ],
                        'responses' => [
                            '200' => [
                                'description' => 'Resposta padrão do proxy',
                                'content' => [
                                    'application/json' => [
                                        'schema' => [
                                            '$ref' => '#/components/schemas/ProxyCallResponse',
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
            'components' => [
                'schemas' => [
                    'ProxyCallRequest' => [
                        'type' => 'object',
                        'properties' => [
                            'args' => [
                                'type' => 'array',
                                'items' => [
                                    'oneOf' => [
                                        ['type' => 'object'],
                                        ['type' => 'array'],
                                        ['type' => 'null'],
                                    ],
                                ],
                                'example' => [
                                    ['id' => 'cus_123'],
                                    ['limit' => 10],
                                    ['Content-Type' => 'application/json'],
                                    ['name' => 'Cliente teste'],
                                ],
                            ],
                            'meta' => [
                                'type' => 'object',
                                'additionalProperties' => true,
                                'example' => [
                                    'api_key' => 'SUA_CHAVE',
                                ],
                            ],
                        ],
                    ],
                    'ProxyCallResponse' => [
                        'type' => 'object',
                        'properties' => [
                            'success' => ['type' => 'boolean'],
                            'duration_ms' => ['type' => 'integer'],
                            'response' => ['nullable' => true],
                            'error' => ['type' => 'string', 'nullable' => true],
                        ],
                    ],
                ],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function postmanCollectionSpec(ServerRequestInterface $request): array
    {
        $baseUrl = '{{base_url}}';

        return [
            'info' => [
                'name' => 'Asaas SDK Proxy',
                'schema' => 'https://schema.getpostman.com/json/collection/v2.1.0/collection.json',
            ],
            'item' => [
                [
                    'name' => 'Catalog',
                    'request' => [
                        'method' => 'GET',
                        'header' => [
                            ['key' => 'X-Asaas-Api-Key', 'value' => '{{asaas_api_key}}'],
                            ['key' => 'X-Asaas-Env', 'value' => '{{asaas_env}}'],
                        ],
                        'url' => [
                            'raw' => $baseUrl . '/api/sdk/catalog',
                            'host' => [$baseUrl],
                            'path' => ['api', 'sdk', 'catalog'],
                        ],
                    ],
                ],
                [
                    'name' => 'Call SDK Method',
                    'request' => [
                        'method' => 'POST',
                        'header' => [
                            ['key' => 'Content-Type', 'value' => 'application/json'],
                            ['key' => 'X-Asaas-Api-Key', 'value' => '{{asaas_api_key}}'],
                            ['key' => 'X-Asaas-Env', 'value' => '{{asaas_env}}'],
                        ],
                        'body' => [
                            'mode' => 'raw',
                            'raw' => json_encode([
                                'args' => [
                                    ['id' => 'cus_123'],
                                    [],
                                    [],
                                    [],
                                ],
                            ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                        ],
                        'url' => [
                            'raw' => $baseUrl . '/api/sdk/call/customer/listCustomers',
                            'host' => [$baseUrl],
                            'path' => ['api', 'sdk', 'call', 'customer', 'listCustomers'],
                        ],
                    ],
                ],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function postmanEnvSpec(ServerRequestInterface $request, string $env): array
    {
        $resolvedEnv = $env === 'prod' || $env === 'production' ? 'production' : 'sandbox';

        return [
            'name' => 'Asaas SDK Proxy (' . $resolvedEnv . ')',
            'values' => [
                [
                    'key' => 'base_url',
                    'value' => $this->buildServerUrl($request),
                    'enabled' => true,
                ],
                [
                    'key' => 'asaas_api_key',
                    'value' => '',
                    'enabled' => true,
                ],
                [
                    'key' => 'asaas_env',
                    'value' => $resolvedEnv,
                    'enabled' => true,
                ],
            ],
        ];
    }

    private function buildServerUrl(ServerRequestInterface $request): string
    {
        $uri = $request->getUri();
        $host = $uri->getHost();
        $scheme = $uri->getScheme() !== '' ? $uri->getScheme() : 'http';
        $port = $uri->getPort();
        $portPart = $port && !in_array($port, [80, 443], true) ? ':' . $port : '';

        return $scheme . '://' . $host . $portPart;
    }
}
