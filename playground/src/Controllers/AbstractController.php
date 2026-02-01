<?php

declare(strict_types=1);

namespace Playground\Controllers;

use Playground\Bootstrap;
use Psr\Http\Message\ResponseInterface;
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
}
