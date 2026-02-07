<?php

declare(strict_types=1);

namespace Playground\Controllers;

use Playground\Utils\ReflectionScanner;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final class ExplorerController extends AbstractController
{
    public function index(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $scanner = new ReflectionScanner($this->bootstrap->basePath());
        $catalog = $scanner->catalog();

        // remove serviços gerados (se existirem) e mantém apenas públicos
        $services = array_values(array_filter(
            $catalog['services'] ?? [],
            fn (string $class): bool => !str_contains($class, '\\Generated\\')
        ));

        // organiza métodos por classe
        $methods = $catalog['methods'] ?? [];

        return $this->view('explorer', [
            'services' => $services,
            'methods' => $methods,
            'sdk_version' => $this->bootstrap->sdkVersion(),
            'default_env' => $this->bootstrap->env('ASAAS_ENV', 'sandbox'),
        ]);
    }

    /**
     * Executa uma chamada da SDK a partir do Explorer.
     * Espera campos do form:
     * - service, method
     * - json (payload)
     * - api_key / asaas_env (opcionais)
     * - files (upload opcional)
     */
    public function run(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $start = microtime(true);
        $success = false;
        $responseData = null;
        $errorMessage = null;

        try {
            $body = (array) $request->getParsedBody();
            $serviceName = (string) ($body['service'] ?? '');
            $methodName = (string) ($body['method'] ?? '');

            if ($serviceName === '' || $methodName === '') {
                throw new \InvalidArgumentException('Informe service e method.');
            }

            $serviceClass = $this->resolveServiceClass($serviceName);
            if ($serviceClass === null || !class_exists($serviceClass)) {
                throw new \RuntimeException('Service inválido.');
            }

            $sdk = $this->bootstrap->sdkForRequest($request, $this->extractApiKey($request));
            $instance = $this->resolveService($serviceClass, $sdk, $request, null);

            if (!method_exists($serviceClass, $methodName)) {
                throw new \RuntimeException('Método inválido.');
            }

            $reflection = new \ReflectionMethod($serviceClass, $methodName);
            if (!$reflection->isPublic() || $reflection->isConstructor() || $reflection->isDestructor()) {
                throw new \RuntimeException('Método inválido.');
            }

            $files = $request->getUploadedFiles();

            $arguments = $this->buildArguments($reflection, $body, $files);

            $responseData = $reflection->invokeArgs($instance, $arguments);
            $success = true;
        } catch (\Throwable $e) {
            $errorMessage = $e->getMessage();
        }

        $duration = (int) ((microtime(true) - $start) * 1000);

        return $this->json([
            'success' => $success,
            'duration_ms' => $duration,
            'response' => $success ? $responseData : null,
            'error' => $success ? null : $errorMessage,
        ], $success ? 200 : 500);
    }

    /**
     * Constrói argumentos para o método da SDK.
     *
     * Regras:
     * - Se o método parece ter assinatura padrão da SDK (pathParams, query, headers, payload)
     *   e o usuário só mandou um JSON simples, usamos esse JSON como payload (4º arg),
     *   MAS: se existir "id" no JSON simples, assumimos que é path param (1º arg)
     *   e removemos do payload.
     */
    private function buildArguments(\ReflectionMethod $reflection, array $payload, array $files): array
    {
        $json = (string) ($payload['json'] ?? '');
        $data = $this->decodeJsonOrEmpty($json);

        // No Explorer, o usuário normalmente quer enviar APENAS o payload (ex.: {"name":"..."}).
        // Antes, isso não batia com os nomes dos parâmetros e o payload era perdido (virava null).
        // Aqui, quando o método tem a assinatura padrão e o JSON não trouxe explicitamente
        // pathParams/query/headers/payload, tratamos o JSON como o 4º argumento (payload).
        if ($this->looksLikeGeneratedSdkSignature($reflection) && !$this->hasAnySdkNamedArg($data)) {
            $direct = $data;

            // ✅ Unwrap genérico: se vier { "customer": {...} } ou { "payment": {...} }
            // (apenas quando houver 1 chave e o valor for array)
            if (count($direct) === 1) {
                $only = array_values($direct)[0] ?? null;
                if (is_array($only)) {
                    $direct = $only;
                }
            }

            // ✅ Se vier "id" no JSON simples, assumimos que é path param (1º arg),
            // e o restante vira payload (4º arg). Resolve update/delete/getById/cancel etc.
            $pathParams = [];
            if (array_key_exists('id', $direct) && (is_string($direct['id']) || is_int($direct['id']))) {
                $pathParams['id'] = (string) $direct['id'];
                unset($direct['id']);
            }

            $payloadOnly = $direct !== [] ? $direct : null;

            return $this->buildPositionalArguments($reflection, [$pathParams, [], [], $payloadOnly], $files);
        }

        // Caso contrário, tenta casar por nome de parâmetro (modo avançado)
        $argsByName = [];
        foreach ($reflection->getParameters() as $p) {
            $name = $p->getName();
            if (array_key_exists($name, $data)) {
                $argsByName[$name] = $data[$name];
            }
        }

        // Se conseguiu argumentos nomeados, usa eles.
        if (!empty($argsByName)) {
            return $this->buildNamedArguments($reflection, $argsByName, $files);
        }

        // Último fallback: tenta passar o JSON inteiro como primeiro argumento (caso o método não seja padrão)
        return $this->buildNamedArguments($reflection, $data, $files);
    }

    private function decodeJsonOrEmpty(string $json): array
    {
        $json = trim($json);
        if ($json === '') {
            return [];
        }

        $decoded = json_decode($json, true);
        return is_array($decoded) ? $decoded : [];
    }

    private function hasAnySdkNamedArg(array $data): bool
    {
        // Se o usuário explicitou os nomes "pathParams/query/headers/payload" (modo avançado),
        // não devemos mexer.
        return array_key_exists('pathParams', $data)
            || array_key_exists('query', $data)
            || array_key_exists('headers', $data)
            || array_key_exists('payload', $data)
            || array_key_exists('args', $data);
    }

    private function looksLikeGeneratedSdkSignature(\ReflectionMethod $reflection): bool
    {
        $params = $reflection->getParameters();
        if (count($params) < 1) {
            return false;
        }

        // Padrão esperado: (array $pathParams = [], array $query = [], array $headers = [], ?array $payload = null)
        // Não valida tipo com rigor absoluto para não quebrar compatibilidade.
        $names = array_map(fn(\ReflectionParameter $p): string => $p->getName(), $params);

        return isset($names[0], $names[1], $names[2]) &&
            $names[0] === 'pathParams' &&
            $names[1] === 'query' &&
            $names[2] === 'headers';
    }

    private function buildPositionalArguments(\ReflectionMethod $reflection, array $positional, array $files): array
    {
        // garante 4 itens
        $positional = array_values($positional);
        $positional = array_pad($positional, 4, null);

        // Se o método aceita payload e há arquivo, preserve comportamento existente (se houver).
        // (mantém idempotência e evita mexer em upload)
        return [
            is_array($positional[0]) ? $positional[0] : [],
            is_array($positional[1]) ? $positional[1] : [],
            is_array($positional[2]) ? $positional[2] : [],
            is_array($positional[3]) ? $positional[3] : (is_null($positional[3]) ? null : (array) $positional[3]),
        ];
    }

    private function buildNamedArguments(\ReflectionMethod $reflection, array $named, array $files): array
    {
        $out = [];
        foreach ($reflection->getParameters() as $p) {
            $name = $p->getName();

            if (array_key_exists($name, $named)) {
                $out[] = $named[$name];
                continue;
            }

            if ($p->isDefaultValueAvailable()) {
                $out[] = $p->getDefaultValue();
                continue;
            }

            $out[] = null;
        }

        return $out;
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

    private function resolveService(string $class, \Asaas\Sdk\AsaasSdk $sdk, ServerRequestInterface $request, ?string $apiKey): object
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
}
