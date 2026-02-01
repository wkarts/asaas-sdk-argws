<?php

declare(strict_types=1);

namespace Asaas\Sdk\Generator;

final class OpenApiBuilder
{
    /**
     * @var callable(string): string
     */
    private \Closure $httpGet;
    
    public function __construct(?callable $httpGet = null)
    {
        $this->httpGet = $httpGet
            ? \Closure::fromCallable($httpGet)
            : function (string $url): string {
                $ctx = stream_context_create([
                    'http' => [
                        'timeout' => 60,
                        'header'  => "User-Agent: asaas-sdk-php-generator/1.0\r\n",
                    ],
                ]);
                $data = @file_get_contents($url, false, $ctx);
                if ($data === false) {
                    throw new \RuntimeException("Falha ao baixar: {$url}");
                }
                return $data;
            };
    }


    /**
     * @return array<string, mixed>
     */
    public function buildFromReference(string $baseUrl): array
    {
        $html = ($this->httpGet)($baseUrl);
        $slugs = $this->extractSlugs($html);

        $merged = [
            'openapi' => '3.0.0',
            'info' => ['title' => 'Asaas API', 'version' => '1.0.0'],
            'paths' => [],
            'components' => [
                'schemas' => [],
                'securitySchemes' => [],
            ],
            'tags' => [],
        ];

        foreach ($slugs as $slug) {
            $markdown = ($this->httpGet)(sprintf('https://docs.asaas.com/reference/%s.md', $slug));
            $doc = $this->extractOpenApiJson($markdown);

            if ($doc === null) {
                continue;
            }

            $merged['paths'] = array_replace_recursive($merged['paths'], $doc['paths'] ?? []);
            $merged['components']['schemas'] = array_replace_recursive(
                $merged['components']['schemas'],
                $doc['components']['schemas'] ?? []
            );
            $merged['components']['securitySchemes'] = array_replace_recursive(
                $merged['components']['securitySchemes'],
                $doc['components']['securitySchemes'] ?? []
            );

            if (isset($doc['tags']) && is_array($doc['tags'])) {
                $merged['tags'] = array_merge($merged['tags'], $doc['tags']);
            }
        }

        return $merged;
    }

    /**
     * @return string[]
     */
    public function extractSlugs(string $html): array
    {
        preg_match_all('#/reference/([a-z0-9\-]+)#i', $html, $matches);

        return array_values(array_unique($matches[1]));
    }

    /**
     * @return array<string, mixed>|null
     */
    public function extractOpenApiJson(string $markdown): ?array
    {
        $pattern = '/```json\s*\n(\{.*?\})\n```/s';
        if (!preg_match($pattern, $markdown, $matches)) {
            return null;
        }

        $json = trim($matches[1]);
        if (!str_contains($markdown, 'OpenAPI definition')) {
            return null;
        }

        $decoded = json_decode($json, true);

        return is_array($decoded) ? $decoded : null;
    }
}
