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
        return $this->fileResponse(
            $this->bootstrap->basePath() . '/public/openapi.json',
            'application/json'
        );
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
        $uri = $request->getUri();
        $host = $uri->getHost();
        $scheme = $uri->getScheme() !== '' ? $uri->getScheme() : 'http';
        $port = $uri->getPort();
        $portPart = $port && !in_array($port, [80, 443], true) ? ':' . $port : '';

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
}
