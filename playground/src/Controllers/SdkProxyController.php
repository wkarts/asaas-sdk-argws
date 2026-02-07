<?php

declare(strict_types=1);

namespace Playground\Controllers;

use Asaas\Sdk\AsaasSdk;
use Playground\Utils\ReflectionScanner;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Psr7\Response;

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

        // Shorthand: se o body não seguir o formato {"args": [...]},
        // tratamos o body como "payload" (4º argumento padrão da SDK).
        // Isso permite usar curl enviando diretamente o objeto do recurso.
        if (!array_key_exists('args', $payload) && $payload !== []) {
            $meta = is_array($payload['meta'] ?? null) ? (array) $payload['meta'] : null;
            $directPayload = $payload;
            unset($directPayload['meta']);

            $payload = [
                'meta' => $meta,
                'args' => [[], [], [], $directPayload],
            ];
        }

        // Permite chamadas GET (sem body) — útil para Swagger/Scalar/Browser.
        if (empty($payload) && strtoupper($request->getMethod()) === 'GET') {
            $qp = $request->getQueryParams();

            // Formato suportado:
            // - ?args=<json>
            // - ?api_key=...&asaas_env=sandbox
            // - ?meta=<json>
            if (isset($qp['args']) && is_string($qp['args']) && $qp['args'] !== '') {
                $decoded = json_decode($qp['args'], true);
                if (is_array($decoded)) {
                    $payload['args'] = $decoded;
                }
            }

            // meta pode vir como JSON (string) ou como query params individuais
            if (isset($qp['meta']) && is_string($qp['meta']) && $qp['meta'] !== '') {
                $decodedMeta = json_decode($qp['meta'], true);
                if (is_array($decodedMeta)) {
                    $payload['meta'] = $decodedMeta;
                }
            }

            foreach (['api_key', 'access_token'] as $k) {
                if (isset($qp[$k]) && is_string($qp[$k]) && $qp[$k] !== '') {
                    $payload[$k] = $qp[$k];
                }
            }
        }
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
        $scanner = new ReflectionScanner($this->bootstrap->basePath());
        $catalog = $scanner->catalog();

        $services = array_values(array_filter(
            $catalog['services'],
            fn(string $class): bool => !str_contains($class, '\\Generated\\')
        ));

        $spec = $this->buildOpenApiSpec($request, $services, $catalog['methods'] ?? []);

        return $this->json($spec);
    }

    public function swagger(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        return $this->fileResponse(
            $this->bootstrap->basePath() . '/public/swagger/index.html',
            'text/html'
        );
    }

    public function scalar(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        return $this->fileResponse(
            $this->bootstrap->basePath() . '/public/scalar/index.html',
            'text/html'
        );
    }

    public function postmanCollection(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        return $this->fileResponse(
            $this->bootstrap->basePath() . '/public/postman/collection.json',
            'application/json'
        );
    }

    public function postmanEnv(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $env = (string) ($args['env'] ?? '');

        $path = $this->bootstrap->basePath() . '/public/postman/env/' . $env . '.json';

        return $this->fileResponse($path, 'application/json');
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

    private function buildServerUrl(ServerRequestInterface $request): string
    {
        // Override manual para ambientes atrás de proxy onde o host público não é inferível.
        // Ex.: PLAYGROUND_PUBLIC_URL=https://playground-asaas-sdk.argws.com.br
        $public = trim((string) getenv('PLAYGROUND_PUBLIC_URL'));
        if ($public !== '') {
            return rtrim($public, '/');
        }

        // Reverse proxy friendly: prioriza headers X-Forwarded-* quando presentes.
        $xfHost = trim($request->getHeaderLine('X-Forwarded-Host'));
        $xfProto = trim($request->getHeaderLine('X-Forwarded-Proto'));
        $xfPort = trim($request->getHeaderLine('X-Forwarded-Port'));

        $uri = $request->getUri();
        $host = $xfHost !== '' ? explode(',', $xfHost)[0] : $uri->getHost();
        $scheme = $xfProto !== '' ? explode(',', $xfProto)[0] : ($uri->getScheme() !== '' ? $uri->getScheme() : 'http');

        $port = null;
        if ($xfPort !== '' && ctype_digit(explode(',', $xfPort)[0])) {
            $port = (int) explode(',', $xfPort)[0];
        } else {
            // Se estamos atrás de proxy (X-Forwarded-Host/Proto), não use a porta interna do container.
            $hasProxySignal = ($xfHost !== '' || $xfProto !== '');
            if (!$hasProxySignal && $uri->getPort() !== null) {
                $port = (int) $uri->getPort();
            }
        }

        $portPart = $port !== null && !in_array($port, [80, 443], true) ? ':' . $port : '';

        return $scheme . '://' . $host . $portPart;
    }

    private function fileResponse(string $path, string $contentType): ResponseInterface
    {
        if (!is_file($path)) {
            return $this->json(['error' => 'Arquivo não encontrado.'], 404);
        }

        $contents = file_get_contents($path);
        if ($contents === false) {
            return $this->json(['error' => 'Falha ao ler arquivo.'], 500);
        }

        $response = new Response();
        $response->getBody()->write($contents);

        return $response->withHeader('Content-Type', $contentType);
    }

    /**
     * Gera um OpenAPI "vivo" baseado na SDK instalada no playground.
     * Assim Swagger UI / Scalar / Postman conseguem enxergar TODOS os recursos.
     *
     * @param array<int, string> $services
     * @param array<string, array<int, string>> $methodsByClass
     * @return array<string, mixed>
     */
    private function buildOpenApiSpec_(ServerRequestInterface $request, array $services, array $methodsByClass): array
    {
        // Para Swagger/Scalar funcionando atrás de reverse proxy, NÃO fixe URL absoluta.
        // Usamos servidor relativo para que o cliente chame a mesma origem (https://host).
        $serverUrl = '/';

        $paths = [];

        // schema padrão de payload (mantém compat com a rota /api/sdk/{service}/{method})
        $requestSchema = [
            'type' => 'object',
            'additionalProperties' => false,
            'properties' => [
                'meta' => [
                    'type' => 'object',
                    'additionalProperties' => true,
                    'description' => 'Metadata opcional. Use meta.api_key para sobrescrever a key do header.',
                    'properties' => [
                        'api_key' => ['type' => 'string'],
                    ],
                ],
                'args' => [
                    'type' => 'array',
                    'minItems' => 0,
                    'maxItems' => 4,
                    'description' => 'Assinatura padrão: [pathParams, query, headers, payload]',
                    'items' => [
                        'oneOf' => [
                            ['type' => 'object', 'additionalProperties' => true],
                            ['type' => 'array'],
                            ['type' => 'string'],
                            ['type' => 'number'],
                            ['type' => 'boolean'],
                            ['type' => 'null'],
                        ],
                    ],
                ],
            ],
        ];

        $responseSchema = [
            'type' => 'object',
            'additionalProperties' => true,
            'properties' => [
                'success' => ['type' => 'boolean'],
                'duration_ms' => ['type' => 'integer'],
                'response' => ['nullable' => true],
                'error' => ['type' => ['string', 'null']],
            ],
        ];

        foreach ($services as $class) {
            $short = (new \ReflectionClass($class))->getShortName();
            $service = lcfirst(str_replace('Service', '', $short));
            $methods = $methodsByClass[$class] ?? [];

            foreach ($methods as $method) {
                // rota concreta (Swagger enxerga) + rota dinâmica (Slim resolve)
                $path = '/api/sdk/' . $service . '/' . $method;

                $paths[$path] = [
                    'post' => [
                        'tags' => ['sdk', $service],
                        'operationId' => $service . '_' . $method,
                        'summary' => $short . '::' . $method,
                        'description' => 'Proxy para ' . $class . '::' . $method,
                        'parameters' => [
                            [
                                'name' => 'X-Asaas-Api-Key',
                                'in' => 'header',
                                'required' => false,
                                'schema' => ['type' => 'string'],
                                'description' => 'API Key do Asaas (alternativamente use meta.api_key).',
                            ],
                            [
                                'name' => 'X-Asaas-Env',
                                'in' => 'header',
                                'required' => false,
                                'schema' => ['type' => 'string', 'enum' => ['production', 'sandbox']],
                                'description' => 'Ambiente (production|sandbox). Se omitido, usa ASAAS_ENV do container.',
                            ],
                        ],
                        'requestBody' => [
                            'required' => false,
                            'content' => [
                                'application/json' => [
                                    'schema' => $requestSchema,
                                    'examples' => [
                                        'simples' => [
                                            'summary' => 'Sem params',
                                            'value' => ['args' => [[], [], [], null]],
                                        ],
                                        'comQuery' => [
                                            'summary' => 'Com query e payload',
                                            'value' => [
                                                'args' => [
                                                    [],
                                                    ['limit' => 10],
                                                    [],
                                                    ['name' => 'Cliente Teste'],
                                                ],
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                        'responses' => [
                            '200' => [
                                'description' => 'OK',
                                'content' => [
                                    'application/json' => [
                                        'schema' => $responseSchema,
                                    ],
                                ],
                            ],
                            '500' => [
                                'description' => 'Erro',
                                'content' => [
                                    'application/json' => [
                                        'schema' => $responseSchema,
                                    ],
                                ],
                            ],
                        ],
                    ],
                ];
            }
        }

        // endpoints utilitários
        $paths['/api/sdk/catalog'] = [
            'get' => [
                'tags' => ['sdk'],
                'operationId' => 'sdk_catalog',
                'summary' => 'Catálogo (services + methods) detectado por Reflection',
                'responses' => [
                    '200' => [
                        'description' => 'OK',
                        'content' => ['application/json' => ['schema' => ['type' => 'object']]],
                    ],
                ],
            ],
        ];

        return [
            'openapi' => '3.0.3',
            'info' => [
                'title' => 'Asaas SDK Playground API',
                'version' => '1.0.0',
                'description' => 'API do Playground que expõe a SDK via proxy com OpenAPI dinâmico.',
            ],
            'servers' => [
                ['url' => $serverUrl, 'description' => 'Host atual'],
            ],
            'paths' => $paths,
        ];
    }

    /**
     * @param string[] $services
     * @param array<string, array<int, array<string, mixed>>> $methods
     * @return array<string, mixed>
     */
    private function buildOpenApiSpec(ServerRequestInterface $request, array $services, array $methods): array
    {
        // Para deixar a UI mais clara, exibimos a URL real do host (incluindo reverse proxy via X-Forwarded-*).
        // Também mantemos um server relativo para garantir compatibilidade em cenários com path-prefix.
        $absoluteServerUrl = $this->buildServerUrl($request);
        $serverUrl = '/';

        $paths = [];

        foreach ($services as $serviceClass) {
            if (!isset($methods[$serviceClass]) || !is_array($methods[$serviceClass])) {
                continue;
            }

            $short = (new \ReflectionClass($serviceClass))->getShortName();
            $serviceSlug = strtolower(preg_replace('/Service$/', '', $short) ?: $short);

            foreach ($methods[$serviceClass] as $m) {
                $methodName = (string) ($m['name'] ?? '');
                if ($methodName === '') {
                    continue;
                }

                $path = '/api/sdk/' . $serviceSlug . '/' . $methodName;
                $paths[$path] = [
                    'get' => [
                        'summary' => $short . '::' . $methodName . ' (GET)',
                        'operationId' => $serviceSlug . '_' . $methodName . '_get',
                        'tags' => [$serviceSlug],
                        'security' => [
                            ['AsaasApiKey' => []],
                        ],
                        'parameters' => [
                            [
                                'name' => 'X-Asaas-Env',
                                'in' => 'header',
                                'required' => false,
                                'schema' => ['type' => 'string', 'enum' => ['sandbox', 'production']],
                                'description' => 'Opcional. Sobrescreve o ambiente do Asaas.',
                            ],
                            [
                                'name' => 'args',
                                'in' => 'query',
                                'required' => false,
                                'schema' => ['type' => 'string'],
                                'description' => 'JSON com os argumentos posicionais: [pathParams, query, headers, payload].',
                            ],
                            [
                                'name' => 'meta',
                                'in' => 'query',
                                'required' => false,
                                'schema' => ['type' => 'string'],
                                'description' => 'JSON com metadados (ex.: {"api_key":"..."}).',
                            ],
                            [
                                'name' => 'api_key',
                                'in' => 'query',
                                'required' => false,
                                'schema' => ['type' => 'string'],
                                'description' => 'Alternativa ao header X-Asaas-Api-Key (somente para facilitar testes no browser).',
                            ],
                        ],
                        'responses' => [
                            '200' => [
                                'description' => 'OK',
                                'content' => [
                                    'application/json' => [
                                        'schema' => [
                                            '$ref' => '#/components/schemas/SdkCallResponse',
                                        ],
                                    ],
                                ],
                            ],
                            '500' => [
                                'description' => 'Erro',
                                'content' => [
                                    'application/json' => [
                                        'schema' => [
                                            '$ref' => '#/components/schemas/SdkCallResponse',
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                    'post' => [
                        'summary' => $short . '::' . $methodName,
                        'operationId' => $serviceSlug . '_' . $methodName,
                        'tags' => [$serviceSlug],
                        'security' => [
                            ['AsaasApiKey' => []],
                        ],
                        'parameters' => [
                            [
                                'name' => 'X-Asaas-Env',
                                'in' => 'header',
                                'required' => false,
                                'schema' => ['type' => 'string', 'enum' => ['sandbox', 'production']],
                                'description' => 'Opcional. Sobrescreve o ambiente do Asaas.',
                            ],
                        ],
                        'requestBody' => [
                            'required' => false,
                            'content' => [
                                'application/json' => [
                                    'schema' => [
                                        '$ref' => '#/components/schemas/SdkCallRequest',
                                    ],
                                ],
                            ],
                        ],
                        'responses' => [
                            '200' => [
                                'description' => 'OK',
                                'content' => [
                                    'application/json' => [
                                        'schema' => [
                                            '$ref' => '#/components/schemas/SdkCallResponse',
                                        ],
                                    ],
                                ],
                            ],
                            '500' => [
                                'description' => 'Erro',
                                'content' => [
                                    'application/json' => [
                                        'schema' => [
                                            '$ref' => '#/components/schemas/SdkCallResponse',
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ];
            }
        }

        return [
            'openapi' => '3.1.0',
            'info' => [
                'title' => 'Asaas SDK Playground API',
                'version' => $this->bootstrap->sdkVersion(),
                'description' => 'API de Playground que expõe os métodos da SDK via proxy.',
            ],
            'servers' => [
                // Primeiro server deve ser relativo para evitar CORS e portas internas atrás de proxy.
                ['url' => $serverUrl, 'description' => 'Host atual (mesma origem) — recomendado'],
                ['url' => $absoluteServerUrl, 'description' => 'Host público (absoluto) — opcional'],
            ],
            'paths' => $paths,
            'components' => [
                'securitySchemes' => [
                    'AsaasApiKey' => [
                        'type' => 'apiKey',
                        'in' => 'header',
                        'name' => 'X-Asaas-Api-Key',
                        'description' => 'Informe sua API Key do Asaas. Alternativas aceitas: X-Asaas-Key, Authorization: Bearer <token>, ou body/meta/api_key em query para GET.',
                    ],
                ],
                'schemas' => [
                    'SdkCallRequest' => [
                        'type' => 'object',
                        'properties' => [
                            'meta' => [
                                'type' => 'object',
                                'description' => 'Metadados do playground (ex.: api_key override).',
                                'properties' => [
                                    'api_key' => ['type' => 'string'],
                                    'access_token' => ['type' => 'string'],
                                ],
                                'additionalProperties' => true,
                            ],
                            'args' => [
                                'type' => 'array',
                                'description' => 'Argumentos posicionais: [pathParams, query, headers, payload].',
                                'items' => [
                                    'anyOf' => [
                                        ['type' => 'object'],
                                        ['type' => 'array'],
                                        ['type' => 'string'],
                                        ['type' => 'number'],
                                        ['type' => 'boolean'],
                                        ['type' => 'null'],
                                    ],
                                ],
                                'minItems' => 0,
                                'maxItems' => 4,
                            ],
                        ],
                        'additionalProperties' => false,
                    ],
                    'SdkCallResponse' => [
                        'type' => 'object',
                        'properties' => [
                            'success' => ['type' => 'boolean'],
                            'duration_ms' => ['type' => 'integer'],
                            'response' => ['type' => ['object', 'array', 'string', 'number', 'boolean', 'null']],
                            'error' => ['type' => ['string', 'null']],
                        ],
                        'required' => ['success', 'duration_ms'],
                        'additionalProperties' => true,
                    ],
                ],
            ],
        ];
    }
}
