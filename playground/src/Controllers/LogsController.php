<?php

declare(strict_types=1);

namespace Playground\Controllers;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final class LogsController extends AbstractController
{
    public function index(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $query = $request->getQueryParams();
        $action = $query['action'] ?? null;
        $success = $query['success'] ?? null;

        $sql = 'SELECT * FROM logs WHERE 1=1';
        $params = [];

        if ($action) {
            $sql .= ' AND action LIKE :action';
            $params['action'] = '%' . $action . '%';
        }

        if ($success !== null && $success !== '') {
            $sql .= ' AND success = :success';
            $params['success'] = (int) $success;
        }

        $sql .= ' ORDER BY id DESC LIMIT 100';

        $db = $this->bootstrap->db()->pdo();
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        $logs = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        return $this->render('logs', [
            'logs' => $logs,
            'filters' => [
                'action' => $action,
                'success' => $success,
            ],
        ]);
    }

    public function show(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $db = $this->bootstrap->db()->pdo();
        $stmt = $db->prepare('SELECT * FROM logs WHERE id = :id');
        $stmt->execute(['id' => $args['id']]);
        $log = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (!$log) {
            return $this->json(['error' => 'Log não encontrado'], 404);
        }

        return $this->render('logs', [
            'logs' => [$log],
            'selected' => $log,
            'filters' => [
                'action' => null,
                'success' => null,
            ],
        ]);
    }
}
