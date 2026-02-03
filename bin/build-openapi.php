<?php

declare(strict_types=1);

use Asaas\Sdk\Generator\OpenApiBuilder;
use GuzzleHttp\Client;
use GuzzleHttp\Pool;

require_once __DIR__ . '/../vendor/autoload.php';

$baseUrl = $argv[1] ?? 'https://docs.asaas.com/reference/comece-por-aqui';
$output = $argv[2] ?? __DIR__ . '/../resources/openapi.json';

// -----------------------------------------------------------------------------
// Determinismo (CI)
// -----------------------------------------------------------------------------
// Por padrão, NÃO atualiza o resources/openapi.json se ele já existir.
// Isso evita falhas no "git diff --exit-code" causadas por mudanças na doc remota.
// Para forçar atualização (manual/local):
//   ASAAS_UPDATE_OPENAPI=1 composer asaas:build-openapi
// ou
//   ASAAS_UPDATE_OPENAPI=1 composer asaas:sdk-build
// -----------------------------------------------------------------------------
if (file_exists($output) && getenv('ASAAS_UPDATE_OPENAPI') !== '1') {
    fwrite(STDOUT, "OpenAPI já existe em {$output}. Pulando download (defina ASAAS_UPDATE_OPENAPI=1 para forçar).\n");
    exit(0);
}

$client = new Client([
    'timeout' => 30,
    'connect_timeout' => 10,
    'headers' => [
        'User-Agent' => 'asaas-sdk-openapi-builder',
    ],
]);
$builder = new OpenApiBuilder();

fwrite(STDOUT, "Baixando referência: {$baseUrl}\n");

try {
    $html = (string) $client->get($baseUrl)->getBody();
    $slugs = $builder->extractSlugs($html);

    $openapi = $builder->emptyDocument();

    $requests = static function (array $slugs) use ($client): Generator {
        foreach ($slugs as $slug) {
            $url = sprintf('https://docs.asaas.com/reference/%s.md', $slug);
            yield static fn() => $client->getAsync($url);
        }
    };

    $pool = new Pool(
        $client,
        $requests($slugs),
        [
            'concurrency' => 10,
            'fulfilled' => static function ($response) use (&$openapi, $builder): void {
                $markdown = (string) $response->getBody();
                $doc = $builder->extractOpenApiJson($markdown);
                if ($doc === null) {
                    return;
                }

                $openapi['paths'] = array_replace_recursive($openapi['paths'], $doc['paths'] ?? []);
                $openapi['components']['schemas'] = array_replace_recursive(
                    $openapi['components']['schemas'],
                    $doc['components']['schemas'] ?? []
                );
                $openapi['components']['securitySchemes'] = array_replace_recursive(
                    $openapi['components']['securitySchemes'],
                    $doc['components']['securitySchemes'] ?? []
                );

                if (isset($doc['tags']) && is_array($doc['tags'])) {
                    $openapi['tags'] = array_merge($openapi['tags'], $doc['tags']);
                }
            },
            'rejected' => static function (): void {
                // Ignora páginas inexistentes ou falhas temporárias.
            },
        ]
    );

    $pool->promise()->wait();

    $paths = $openapi['paths'] ?? [];
    $schemas = $openapi['components']['schemas'] ?? [];
    if ($paths === [] && $schemas === []) {
        throw new RuntimeException('OpenAPI vazio ou referência indisponível.');
    }
} catch (Throwable $exception) {
    fwrite(STDERR, "Falha ao construir OpenAPI: {$exception->getMessage()}\n");
    exit(1);
}

$encoded = json_encode($openapi, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

if ($encoded === false) {
    fwrite(STDERR, "Falha ao serializar OpenAPI.\n");
    exit(1);
}

file_put_contents($output, $encoded . PHP_EOL);

fwrite(STDOUT, "OpenAPI salvo em {$output}\n");
