<?php

declare(strict_types=1);

/**
 * Gera um relatório simples de auditoria (markdown) para CI.
 *
 * Não substitui o CI, apenas padroniza um resumo (útil para anexar como artifact).
 */

function sdkVersion(): string
{
    if (class_exists('Composer\\InstalledVersions')) {
        try {
            /** @var class-string $c */
            $c = 'Composer\\InstalledVersions';
            $pretty = $c::getPrettyVersion('argws/asaas-sdk-php');
            if (is_string($pretty) && $pretty !== '') {
                return $pretty;
            }
        } catch (Throwable) {
        }
    }

    return getenv('ASAAS_SDK_VERSION') ?: 'dev';
}

function check(string $label, bool $ok, string $details = ''): array
{
    return ['label' => $label, 'ok' => $ok, 'details' => $details];
}

$base = dirname(__DIR__);
$checks = [];

$checks[] = check('resources/openapi.json existe', is_file($base . '/resources/openapi.json'));
$checks[] = check('Gerados (src/Service/Generated) existem', is_dir($base . '/src/Service/Generated'));
$checks[] = check('Playground expõe swagger', is_file($base . '/playground/views/swagger.php'));
$checks[] = check('Playground expõe openapi.json', is_file($base . '/playground/src/Controllers/SdkProxyController.php'));
$checks[] = check('Checklist oficial existe', is_file($base . '/audit/checklists/SDK_PLAYGROUND_CHECKLIST.md'));

$allOk = true;
foreach ($checks as $c) {
    $allOk = $allOk && (bool) $c['ok'];
}

$ts = date('Y-m-d H:i:s');
$sha = getenv('GITHUB_SHA') ?: '';
$ref = getenv('GITHUB_REF_NAME') ?: '';

$md = [];
$md[] = '# Relatório de Auditoria — SDK + Playground';
$md[] = '';
$md[] = '- Data: `' . $ts . '`';
$md[] = '- SDK version: `' . sdkVersion() . '`';
if ($ref !== '') {
    $md[] = '- Ref: `' . $ref . '`';
}
if ($sha !== '') {
    $md[] = '- SHA: `' . substr($sha, 0, 12) . '`';
}
$md[] = '';
$md[] = '## Checks';
$md[] = '';
foreach ($checks as $c) {
    $md[] = sprintf('- %s %s%s', $c['ok'] ? '✅' : '❌', $c['label'], $c['details'] ? ' — ' . $c['details'] : '');
}
$md[] = '';
$md[] = 'Status geral: ' . ($allOk ? '✅ OK' : '❌ FALHOU');
$md[] = '';

$reportDir = $base . '/audit/reports';
if (!is_dir($reportDir)) {
    @mkdir($reportDir, 0o777, true);
}

$filename = $reportDir . '/' . date('Ymd_His') . ($sha ? '_' . substr($sha, 0, 8) : '') . '.md';
file_put_contents($filename, implode("\n", $md));

// Saída no summary do GitHub, quando disponível
$summary = getenv('GITHUB_STEP_SUMMARY');
if (is_string($summary) && $summary !== '') {
    file_put_contents($summary, implode("\n", $md), FILE_APPEND);
}

fwrite(STDOUT, "Relatório gerado em: {$filename}\n");

if (!$allOk) {
    exit(2);
}
