<?php

declare(strict_types=1);

namespace Playground\Controllers;

use Asaas\Sdk\AsaasSdk;
use Playground\Utils\ArgumentHydrator;
use Playground\Utils\FileStore;
use Playground\Utils\Json;
use Playground\Utils\ReflectionScanner;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final class ExplorerController extends AbstractController
{
    public function index(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $scanner = new ReflectionScanner($this->bootstrap->basePath());
        $catalog = $scanner->catalog();

        return $this->render('explorer', [
            'catalog' => $catalog,
        ]);
    }

    public function catalog(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $scanner = new ReflectionScanner($this->bootstrap->basePath());

        return $this->json($scanner->catalog());
    }

    public function run(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $data = (array) $request->getParsedBody();
        $class = (string) ($data['class'] ?? '');
        $method = (string) ($data['method'] ?? '');
        $paramsJson = (string) ($data['params'] ?? '');

        $decoded = Json::decode($paramsJson);
        if ($decoded['error']) {
            return $this->json(['error' => $decoded['error']], 422);
        }

        $paramsData = is_array($decoded['data']) ? $decoded['data'] : null;
        $apiKey = $this->extractApiKey($request, $paramsData);

        $start = microtime(true);
        $status = null;
        $responseData = null;
        $errorMessage = null;
        $success = false;

        try {
            if (!class_exists($class)) {
                throw new \RuntimeException('Classe inválida.');
            }

            $sdk = $this->bootstrap->sdkForRequest($request, $apiKey);
            $instance = $this->resolveService($class, $sdk, $request, $apiKey);
            $reflection = new \ReflectionMethod($class, $method);
            $args = $this->buildArguments($reflection, $paramsData, $request);

            $responseData = $reflection->invokeArgs($instance, $args);
            $success = true;
        } catch (\Throwable $exception) {
            $errorMessage = $exception->getMessage();
        }

        $duration = (int) ((microtime(true) - $start) * 1000);
        $result = [
            'success' => $success,
            'duration_ms' => $duration,
            'response' => null,
            'download' => null,
            'error' => $errorMessage,
        ];

        if ($success) {
            $result['response'] = $responseData;
            if (is_string($responseData)) {
                $decodedString = Json::decode($responseData);
                if ($decodedString['error']) {
                    $fileStore = new FileStore($this->bootstrap->basePath());
                    $filename = $fileStore->save($responseData);
                    $result['download'] = '/downloads/' . $filename;
                    $result['response'] = 'Arquivo salvo para download.';
                } else {
                    $result['response'] = $decodedString['data'];
                }
            }
        }

        $this->logAction(
            'EXPLORER:' . $class . '::' . $method,
            $this->scrubSensitive($paramsData),
            $duration,
            $success,
            $status,
            $errorMessage,
            $result['response']
        );

        return $this->json($result, $success ? 200 : 500);
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

    private function buildArguments(\ReflectionMethod $reflection, mixed $data, ServerRequestInterface $request): array
    {
        $files = $request->getUploadedFiles();
        if (!is_array($data)) {
            return [];
        }

        $isAssoc = array_keys($data) !== range(0, count($data) - 1);
        if (!$isAssoc) {
            return $this->buildPositionalArguments($reflection, $data, $files);
        }

        // Compat: a SDK gerada expõe assinatura padrão:
        //   method(array $pathParams = [], array $query = [], array $headers = [], ?array $payload = null)
        // No Explorer, o usuário normalmente quer enviar APENAS o payload (ex.: {"name":"..."}).
        // Antes, isso não batia com os nomes dos parâmetros e o payload era perdido (virava null).
        // Aqui, quando o método tem a assinatura padrão e o JSON não trouxe explicitamente
        // pathParams/query/headers/payload, tratamos o JSON como o 4º argumento (payload).
        //
        // ✅ PATCH MÍNIMO:
        // - se vier "id" no JSON simples, mover para pathParams['id']
        // - remover "id" do payload
        // - payload fica o resto ou null (para delete/cancel etc.)
        // - unwrap opcional de 1 nível quando houver 1 chave e o valor for array (ex.: {"customer":{...}})
        if ($this->looksLikeGeneratedSdkSignature($reflection) && !$this->hasAnySdkNamedArg($data)) {
            $direct = $data;

            // unwrap opcional (somente 1 nível e só quando houver 1 chave)
            // ex.: {"customer": {...}} ou {"payment": {...}}
            if (count($direct) === 1) {
                $only = array_values($direct)[0] ?? null;
                if (is_array($only)) {
                    $direct = $only;
                }
            }

            $pathParams = [];

            if (array_key_exists('id', $direct) && (is_string($direct['id']) || is_int($direct['id']))) {
                $pathParams['id'] = (string) $direct['id'];
                unset($direct['id']);
            }

            $payloadOnly = $direct !== [] ? $direct : null;

            return $this->buildPositionalArguments($reflection, [$pathParams, [], [], $payloadOnly], $files);
        }

        return $this->buildNamedArguments($reflection, $data, $files);
    }

    /**
     * Detecta a assinatura padrão do gerador (pathParams, query, headers, payload).
     * Mantemos isso super conservador para não quebrar métodos "manuais".
     */
    private function looksLikeGeneratedSdkSignature(\ReflectionMethod $reflection): bool
    {
        $params = $reflection->getParameters();
        if (count($params) < 4) {
            return false;
        }

        return ($params[0]->getName() === 'pathParams')
            && ($params[1]->getName() === 'query')
            && ($params[2]->getName() === 'headers')
            && ($params[3]->getName() === 'payload');
    }

    /**
     * Verifica se o JSON já trouxe explicitamente algum dos nomes da assinatura padrão.
     * Se trouxe, respeitamos e deixamos o fluxo original (named args).
     *
     * @param array<string, mixed> $data
     */
    private function hasAnySdkNamedArg(array $data): bool
    {
        foreach (['pathParams', 'query', 'headers', 'payload', 'args'] as $k) {
            if (array_key_exists($k, $data)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param array<string, mixed> $data
     * @param array<string, mixed> $files
     */
    private function buildNamedArguments(\ReflectionMethod $reflection, array $data, array $files): array
    {
        $args = [];
        $hydrator = new ArgumentHydrator();

        foreach ($reflection->getParameters() as $parameter) {
            $name = $parameter->getName();
            if (array_key_exists($name, $files) && $this->looksLikeFile($name)) {
                $uploaded = $files[$name];
                $stream = $uploaded->getStream()->detach();
                $args[] = $stream ?: $uploaded->getStream();
                continue;
            }

            if (!array_key_exists($name, $data)) {
                $args[] = $parameter->isDefaultValueAvailable() ? $parameter->getDefaultValue() : null;
                continue;
            }

            $value = $data[$name];
            $type = $parameter->getType();
            if ($type instanceof \ReflectionNamedType && !$type->isBuiltin() && is_array($value)) {
                $args[] = $hydrator->hydrate($type->getName(), $value);
                continue;
            }

            $args[] = $value;
        }

        return $args;
    }

    /**
     * @param array<int, mixed> $data
     * @param array<string, mixed> $files
     */
    private function buildPositionalArguments(\ReflectionMethod $reflection, array $data, array $files): array
    {
        $args = [];
        $hydrator = new ArgumentHydrator();

        foreach ($reflection->getParameters() as $index => $parameter) {
            $name = $parameter->getName();
            if (array_key_exists($name, $files) && $this->looksLikeFile($name)) {
                $uploaded = $files[$name];
                $stream = $uploaded->getStream()->detach();
                $args[] = $stream ?: $uploaded->getStream();
                continue;
            }

            $value = $data[$index] ?? ($parameter->isDefaultValueAvailable() ? $parameter->getDefaultValue() : null);
            $type = $parameter->getType();
            if ($type instanceof \ReflectionNamedType && !$type->isBuiltin() && is_array($value)) {
                $args[] = $hydrator->hydrate($type->getName(), $value);
                continue;
            }

            $args[] = $value;
        }

        return $args;
    }

    private function looksLikeFile(string $name): bool
    {
        $name = strtolower($name);
        return str_contains($name, 'file') || str_contains($name, 'path')
            || str_contains($name, 'attachment') || str_contains($name, 'upload');
    }

    private function logAction(
        string $action,
        mixed $params,
        int $duration,
        bool $success,
        ?int $httpStatus,
        ?string $errorMessage,
        mixed $response
    ): void {
        $db = $this->bootstrap->db()->pdo();
        $stmt = $db->prepare(
            'INSERT INTO logs (created_at, action, params_json, duration_ms, success, http_status, error_message, response_excerpt) '
            . 'VALUES (:created_at, :action, :params_json, :duration_ms, :success, :http_status, :error_message, :response_excerpt)'
        );

        $stmt->execute([
            'created_at' => date('c'),
            'action' => $action,
            'params_json' => json_encode($params),
            'duration_ms' => $duration,
            'success' => $success ? 1 : 0,
            'http_status' => $httpStatus,
            'error_message' => $errorMessage,
            'response_excerpt' => substr(json_encode($response) ?: '', 0, 2000),
        ]);
    }
}
