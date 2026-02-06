<?php

declare(strict_types=1);

namespace Playground\Controllers;

use Playground\Bootstrap;
use Playground\Utils\Mask;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Psr7\Response;

abstract class AbstractController
{
    public function __construct(protected Bootstrap $bootstrap) {}

    /**
     * @param array<string, mixed> $data
     */
    protected function json(array $data, int $status = 200): ResponseInterface
    {
        $response = new Response($status);
        $response->getBody()->write(json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) ?: '{}');

        return $response->withHeader('Content-Type', 'application/json');
    }

    /**
     * @param array<string, mixed> $params
     */
    protected function render(string $view, array $params = []): ResponseInterface
    {
        extract($params, EXTR_SKIP);
        ob_start();
        require $this->bootstrap->basePath() . '/views/' . $view . '.php';
        $content = ob_get_clean();

        $response = new Response();
        $response->getBody()->write($content ?: '');

        return $response;
    }

    /**
     * @param array<string, mixed>|null $payload
     */
    protected function extractApiKey(ServerRequestInterface $request, ?array &$payload): ?string
    {
        // Header padrão do playground
        foreach (['X-Asaas-Api-Key', 'X-Asaas-Key'] as $headerName) {
            $headerKey = $request->getHeaderLine($headerName);
            if ($headerKey !== '') {
                return $headerKey;
            }
        }

        // Authorization: Bearer <token>
        $auth = $request->getHeaderLine('Authorization');
        if ($auth !== '' && preg_match('/^Bearer\s+(.+)$/i', $auth, $m)) {
            return trim((string) ($m[1] ?? '')) ?: null;
        }

        if (is_array($payload)) {
            foreach (['api_key', 'access_token'] as $field) {
                if (array_key_exists($field, $payload)) {
                    $apiKey = (string) $payload[$field];
                    unset($payload[$field]);
                    return $apiKey;
                }
            }
        }

        return null;
    }

    /**
     * @param array<string, mixed>|null $payload
     * @return array<string, mixed>|null
     */
    protected function scrubSensitive(?array $payload): ?array
    {
        if ($payload === null) {
            return null;
        }

        return Mask::scrubArray($payload);
    }
}
