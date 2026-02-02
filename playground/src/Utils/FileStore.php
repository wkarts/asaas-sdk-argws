<?php

declare(strict_types=1);

namespace Playground\Utils;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final class FileStore
{
    private string $downloadPath;

    public function __construct(string $basePath)
    {
        $this->downloadPath = rtrim($basePath, '/') . '/storage/downloads';
        if (!is_dir($this->downloadPath)) {
            mkdir($this->downloadPath, 0o777, true);
        }
    }

    public function save(string $contents, string $extension = 'bin'): string
    {
        $filename = date('Ymd_His') . '_' . bin2hex(random_bytes(6)) . '.' . $extension;
        file_put_contents($this->downloadPath . '/' . $filename, $contents);

        return $filename;
    }

    public function download(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $name = basename((string) ($args['name'] ?? ''));
        $path = $this->downloadPath . '/' . $name;

        if (!is_file($path)) {
            $response->getBody()->write('Arquivo não encontrado');
            return $response->withStatus(404);
        }

        $stream = fopen($path, 'rb');
        if ($stream === false) {
            $response->getBody()->write('Erro ao abrir arquivo');
            return $response->withStatus(500);
        }

        return $response
            ->withHeader('Content-Type', 'application/octet-stream')
            ->withHeader('Content-Disposition', 'attachment; filename="' . $name . '"')
            ->withBody(new \Slim\Psr7\Stream($stream));
    }
}
