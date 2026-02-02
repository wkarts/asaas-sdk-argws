<?php

declare(strict_types=1);

namespace Playground\Controllers;

use Playground\Utils\Json;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final class WebhookController extends AbstractController
{
    public function index(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $db = $this->bootstrap->db()->pdo();
        $stmt = $db->query('SELECT * FROM webhooks ORDER BY id DESC LIMIT 50');
        $webhooks = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        return $this->render('webhooks', [
            'webhooks' => $webhooks,
            'selected' => null,
        ]);
    }

    public function show(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $db = $this->bootstrap->db()->pdo();
        $stmt = $db->prepare('SELECT * FROM webhooks WHERE id = :id');
        $stmt->execute(['id' => $args['id']]);
        $webhook = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (!$webhook) {
            return $this->json(['error' => 'Webhook não encontrado'], 404);
        }

        $stmt = $db->query('SELECT * FROM webhooks ORDER BY id DESC LIMIT 50');
        $webhooks = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        return $this->render('webhooks', [
            'webhooks' => $webhooks,
            'selected' => $webhook,
        ]);
    }

    public function markProcessed(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $db = $this->bootstrap->db()->pdo();
        $stmt = $db->prepare('UPDATE webhooks SET processed_at = :processed_at WHERE id = :id');
        $stmt->execute([
            'processed_at' => date('c'),
            'id' => $args['id'],
        ]);

        return $this->json(['ok' => true]);
    }

    public function receive(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $body = (string) $request->getBody();
        $payload = Json::decode($body);

        $expected = $this->bootstrap->env('ASAAS_WEBHOOK_TOKEN', '');
        $provided = $request->getHeaderLine('asaas-access-token');
        $tokenStatus = self::tokenStatus($expected, $provided);

        if (!$tokenStatus['authorized']) {
            return $this->json([
                'error' => 'Token inválido',
                'validated_token' => $tokenStatus['validated'],
            ], $tokenStatus['status']);
        }

        $headers = [];
        foreach ($request->getHeaders() as $name => $values) {
            $headers[$name] = implode(',', $values);
        }

        $db = $this->bootstrap->db()->pdo();
        $stmt = $db->prepare(
            'INSERT INTO webhooks (received_at, headers_json, payload_json, validated_token, ip, user_agent, processed_at) '
            . 'VALUES (:received_at, :headers_json, :payload_json, :validated_token, :ip, :user_agent, :processed_at)'
        );

        $stmt->execute([
            'received_at' => date('c'),
            'headers_json' => json_encode($headers),
            'payload_json' => json_encode($payload['data']),
            'validated_token' => $tokenStatus['validated'] ? 1 : 0,
            'ip' => $request->getServerParams()['REMOTE_ADDR'] ?? null,
            'user_agent' => $request->getHeaderLine('User-Agent'),
            'processed_at' => null,
        ]);

        return $this->json([
            'ok' => true,
            'validated_token' => $tokenStatus['validated'],
        ]);
    }

    /**
     * @return array{authorized: bool, validated: bool, status: int}
     */
    public static function tokenStatus(string $expected, ?string $provided): array
    {
        if ($expected === '') {
            return [
                'authorized' => true,
                'validated' => false,
                'status' => 200,
            ];
        }

        if ($provided === $expected) {
            return [
                'authorized' => true,
                'validated' => true,
                'status' => 200,
            ];
        }

        return [
            'authorized' => false,
            'validated' => false,
            'status' => 401,
        ];
    }
}
