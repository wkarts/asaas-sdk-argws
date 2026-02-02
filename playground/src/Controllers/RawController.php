<?php

declare(strict_types=1);

namespace Playground\Controllers;

use Playground\Utils\FileStore;
use Playground\Utils\Json;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final class RawController extends AbstractController
{
    public function index(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        return $this->render('raw');
    }

    public function run(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $data = (array) $request->getParsedBody();
        $method = strtoupper((string) ($data['method'] ?? 'GET'));
        $path = (string) ($data['path'] ?? '/');
        $bodyJson = (string) ($data['body'] ?? '');

        $decoded = Json::decode($bodyJson);
        if ($decoded['error']) {
            return $this->json(['error' => $decoded['error']], 422);
        }

        $payload = is_array($decoded['data']) ? $decoded['data'] : null;
        $apiKey = $this->extractApiKey($request, $payload);

        $start = microtime(true);
        $success = false;
        $responseData = null;
        $errorMessage = null;
        $download = null;

        try {
            $client = $this->bootstrap->clientForRequest($request, $apiKey);
            $responseData = $client->request($method, $path, [], [], $payload);
            $success = true;
        } catch (\Throwable $exception) {
            $errorMessage = $exception->getMessage();
        }

        $duration = (int) ((microtime(true) - $start) * 1000);
        if ($success && is_string($responseData)) {
            $decodedString = Json::decode($responseData);
            if ($decodedString['error']) {
                $fileStore = new FileStore($this->bootstrap->basePath());
                $filename = $fileStore->save($responseData);
                $download = '/downloads/' . $filename;
                $responseData = 'Arquivo salvo para download.';
            } else {
                $responseData = $decodedString['data'];
            }
        }

        $this->logAction(
            'RAW:' . $method . ':' . $path,
            $this->scrubSensitive($payload),
            $duration,
            $success,
            $errorMessage,
            $responseData
        );

        return $this->json([
            'success' => $success,
            'duration_ms' => $duration,
            'response' => $responseData,
            'download' => $download,
            'error' => $errorMessage,
        ], $success ? 200 : 500);
    }

    private function logAction(
        string $action,
        mixed $params,
        int $duration,
        bool $success,
        ?string $errorMessage,
        mixed $response
    ): void {
        $db = $this->bootstrap->db()->pdo();
        $stmt = $db->prepare(
            'INSERT INTO logs (created_at, action, params_json, duration_ms, success, http_status, error_message, response_excerpt) '
            . 'VALUES (:created_at, :action, :params_json, :duration_ms, :success, :http_status, :error_message, :response_excerpt)'
        );

        $stmt->execute([
            'created_at' => date('c'),
            'action' => $action,
            'params_json' => json_encode($params),
            'duration_ms' => $duration,
            'success' => $success ? 1 : 0,
            'http_status' => null,
            'error_message' => $errorMessage,
            'response_excerpt' => substr(json_encode($response) ?: '', 0, 2000),
        ]);
    }
}
