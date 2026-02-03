<?php

declare(strict_types=1);

namespace Asaas\Sdk\Generator;

final class SdkGenerator
{
    /**
     * @param array<string, mixed> $openapi
     * @param array<string, string> $tagMap
     */
    public function generate(
        array $openapi,
        array $tagMap,
        string $serviceDir,
        string $modelDir,
        string $templateDir
    ): void {
        $paths = $openapi['paths'] ?? [];
        $schemas = $openapi['components']['schemas'] ?? [];

        $serviceTemplates = file_get_contents($templateDir . '/service.php.tpl') ?: '';
        $dtoTemplate = file_get_contents($templateDir . '/dto.php.tpl') ?: '';
        $enumTemplate = file_get_contents($templateDir . '/enum.php.tpl') ?: '';

        $operationsByService = [];

        foreach ($paths as $path => $methods) {
            foreach ($methods as $httpMethod => $operation) {
                if (!is_array($operation)) {
                    continue;
                }

                $tag = $operation['tags'][0] ?? 'Default';
                $serviceClass = $tagMap[$tag] ?? $this->fallbackService($tag);

                $operationsByService[$serviceClass][] = [
                    'path' => $path,
                    'method' => strtoupper($httpMethod),
                    'operationId' => (string) ($operation['operationId'] ?? $httpMethod . $path),
                    'requestBody' => $operation['requestBody'] ?? null,
                    'responses' => $operation['responses'] ?? [],
                ];
            }
        }

        foreach ($operationsByService as $serviceClass => $operations) {
            $methods = [];
            $names = [];
            foreach ($operations as $operation) {
                $methodName = $this->normalizeOperationId($operation['operationId']);
                if (in_array($methodName, $names, true)) {
                    $methodName .= strtolower($operation['method']);
                }
                $names[] = $methodName;

                $expectsBinary = $this->responseExpectsBinary($operation['responses']);
                $multipart = $this->requestIsMultipart($operation['requestBody']);

                $methods[] = $this->renderMethod(
                    $methodName,
                    $operation['method'],
                    $operation['path'],
                    $multipart,
                    $expectsBinary
                );
            }

            $classCode = str_replace(
                ['{{className}}', '{{methods}}'],
                [$serviceClass, implode("\n", $methods)],
                $serviceTemplates
            );

            file_put_contents($serviceDir . '/' . $serviceClass . '.php', $classCode);
        }

        foreach ($schemas as $name => $schema) {
            if (!is_array($schema)) {
                continue;
            }

            if (isset($schema['enum'])) {
                $cases = [];
                foreach ($schema['enum'] as $case) {
                    $caseName = strtoupper(preg_replace('/[^a-zA-Z0-9]+/', '_', (string) $case));
                    $cases[] = sprintf('    case %s = %s;', $caseName, var_export($case, true));
                }
                $code = str_replace(
                    ['{{className}}', '{{cases}}'],
                    [$this->studly($name), implode("\n", $cases)],
                    $enumTemplate
                );
                file_put_contents($modelDir . '/' . $this->studly($name) . '.php', $code);
                continue;
            }

            if (($schema['type'] ?? '') !== 'object') {
                continue;
            }

            $properties = $schema['properties'] ?? [];
            $required = $schema['required'] ?? [];
            $propertyLines = [];
            $constructorArgs = [];
            $constructorBody = [];

            foreach ($properties as $propName => $definition) {
                $type = $this->mapType($definition);
                $nullable = !in_array($propName, $required, true);
                $propertyLines[] = sprintf('    public %s$%s;', $nullable ? '?' . $type . ' ' : $type . ' ', $propName);
                $constructorArgs[] = sprintf('%s$%s', $nullable ? '?' . $type . ' ' : $type . ' ', $propName);
                $constructorBody[] = sprintf('        $this->%s = $%s;', $propName, $propName);
            }

            $classCode = str_replace(
                ['{{className}}', '{{properties}}', '{{constructorSignature}}', '{{constructorBody}}'],
                [
                    $this->studly($name),
                    implode("\n", $propertyLines),
                    implode(', ', $constructorArgs),
                    implode("\n", $constructorBody),
                ],
                $dtoTemplate
            );

            file_put_contents($modelDir . '/' . $this->studly($name) . '.php', $classCode);
        }
    }

    private function renderMethod(
        string $methodName,
        string $httpMethod,
        string $path,
        bool $multipart,
        bool $expectsBinary
    ): string {
        $template = <<<'PHP'
                /**
                 * @param array<string, string> $pathParams
                 * @param array<string, mixed> $query
                 * @param array<string, string> $headers
                 * @param array<string, mixed>|null $payload
                 */
                public function {{methodName}}(
                    array $pathParams = [],
                    array $query = [],
                    array $headers = [],
                    ?array $payload = null
                ): mixed {
                    return $this->request(
                        '{{httpMethod}}',
                        '{{path}}',
                        $pathParams,
                        $query,
                        $headers,
                        $payload,
                        {{multipart}},
                        {{expectsBinary}}
                    );
                }

            PHP;

        return str_replace(
            ['{{methodName}}', '{{httpMethod}}', '{{path}}', '{{multipart}}', '{{expectsBinary}}'],
            [
                $methodName,
                $httpMethod,
                $path,
                $multipart ? 'true' : 'false',
                $expectsBinary ? 'true' : 'false',
            ],
            $template
        );
    }

    private function normalizeOperationId(string $operationId): string
    {
        $segments = preg_split('/[^a-zA-Z0-9]+/', $operationId) ?: [];
        $segments = array_filter($segments, static fn(string $segment): bool => $segment !== '');
        $segments = array_map(
            static fn(string $segment): string => ucfirst($segment),
            $segments
        );
        $operationId = implode('', $segments);

        return lcfirst($operationId);
    }

    /**
     * @param array<string, mixed> $responses
     */
    private function responseExpectsBinary(array $responses): bool
    {
        foreach ($responses as $response) {
            if (!is_array($response)) {
                continue;
            }
            $content = $response['content'] ?? [];
            if (isset($content['application/pdf']) || isset($content['application/octet-stream'])) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param array<string, mixed>|null $requestBody
     */
    private function requestIsMultipart(?array $requestBody): bool
    {
        if ($requestBody === null) {
            return false;
        }
        $content = $requestBody['content'] ?? [];

        return isset($content['multipart/form-data']);
    }

    private function fallbackService(string $tag): string
    {
        return $this->studly($tag) . 'Service';
    }

    private function studly(string $value): string
    {
        $value = preg_replace('/[^a-zA-Z0-9]+/', ' ', $value) ?? $value;
        $value = str_replace(' ', '', ucwords(strtolower($value)));

        return $value;
    }

    private function mapType(mixed $schema): string
    {
        if (!is_array($schema)) {
            return 'mixed';
        }

        if (isset($schema['$ref'])) {
            $parts = explode('/', (string) $schema['$ref']);
            return $this->studly(end($parts));
        }

        return match ($schema['type'] ?? 'mixed') {
            'string' => 'string',
            'integer' => 'int',
            'number' => 'float',
            'boolean' => 'bool',
            'array' => 'array',
            'object' => 'array',
            default => 'mixed',
        };
    }
}
