<?php

declare(strict_types=1);

use Asaas\Sdk\Generator\ParityVerifier;

require_once __DIR__ . '/../vendor/autoload.php';

$verifier = new ParityVerifier();
$expected = $verifier->expectedServices();
$actual = $verifier->currentServices();

sort($expected);

$missing = array_values(array_diff($expected, $actual));
$extra = array_values(array_diff($actual, $expected));

if ($missing !== []) {
    fwrite(STDERR, "Serviços ausentes no facade: " . implode(', ', $missing) . "\n");
}

if ($extra !== []) {
    fwrite(STDERR, "Serviços extras no facade: " . implode(', ', $extra) . "\n");
}

if ($missing !== [] || $extra !== []) {
    exit(1);
}

fwrite(STDOUT, "Paridade OK.\n");
