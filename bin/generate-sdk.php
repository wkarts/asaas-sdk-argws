<?php

declare(strict_types=1);

use Asaas\Sdk\Generator\SdkGenerator;

require_once __DIR__ . '/../vendor/autoload.php';

$openapiFile = $argv[1] ?? __DIR__ . '/../resources/openapi.json';
$tagMapFile = $argv[2] ?? __DIR__ . '/../resources/tag-map.php';
$serviceDir = $argv[3] ?? __DIR__ . '/../src/Service/Generated';
$modelDir = $argv[4] ?? __DIR__ . '/../src/Model/Generated';
$templateDir = $argv[5] ?? __DIR__ . '/../resources/templates';

if (!file_exists($openapiFile)) {
    fwrite(STDERR, "Arquivo OpenAPI não encontrado: {$openapiFile}\n");
    exit(1);
}

$openapi = json_decode((string) file_get_contents($openapiFile), true);
if (!is_array($openapi)) {
    fwrite(STDERR, "OpenAPI inválido.\n");
    exit(1);
}

$tagMap = require $tagMapFile;

$generator = new SdkGenerator();
$generator->generate($openapi, $tagMap, $serviceDir, $modelDir, $templateDir);

fwrite(STDOUT, "SDK gerada com sucesso.\n");
