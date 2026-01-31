<?php

declare(strict_types=1);

use Asaas\Sdk\Generator\OpenApiBuilder;

require_once __DIR__ . '/../vendor/autoload.php';

$baseUrl = $argv[1] ?? 'https://docs.asaas.com/reference/comece-por-aqui';
$output = $argv[2] ?? __DIR__ . '/../resources/openapi.json';

$builder = new OpenApiBuilder();

fwrite(STDOUT, "Baixando referência: {$baseUrl}\n");

try {
    $openapi = $builder->buildFromReference($baseUrl);
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
