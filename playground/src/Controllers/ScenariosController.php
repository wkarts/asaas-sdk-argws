<?php

declare(strict_types=1);

namespace Playground\Controllers;

use Playground\Storage\KvStore;
use Playground\Utils\Json;
use Playground\Utils\Mask;
use Playground\Utils\ReflectionScanner;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final class ScenariosController extends AbstractController
{
    public function index(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        return $this->render('scenarios');
    }

    public function run(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $data = (array) $request->getParsedBody();
        $action = (string) ($data['action'] ?? '');
        $payload = Json::decode((string) ($data['params'] ?? ''));

        if ($payload['error']) {
            return $this->json(['error' => $payload['error']], 422);
        }

        $scanner = new ReflectionScanner($this->bootstrap->basePath());
        $catalog = $scanner->catalog();
        $kv = new KvStore($this->bootstrap->db());

        $start = microtime(true);
        $success = false;
        $responseData = null;
        $errorMessage = null;

        try {
            switch ($action) {
                case 'create_customer':
                    $responseData = $this->runCreateCustomer($catalog, $payload['data'] ?? [], $kv);
                    break;
                case 'list_customers':
                    $responseData = $this->runListCustomers($catalog, $payload['data'] ?? []);
                    break;
                case 'create_payment':
                    $responseData = $this->runCreatePayment($catalog, $payload['data'] ?? [], $kv);
                    break;
                case 'list_payments':
                    $responseData = $this->runListPayments($catalog, $payload['data'] ?? []);
                    break;
                case 'cancel_payment':
                    $responseData = $this->runCancelPayment($catalog, $payload['data'] ?? [], $kv);
                    break;
                default:
                    throw new \RuntimeException('Ação inválida.');
            }
            $success = true;
        } catch (\Throwable $exception) {
            $errorMessage = $exception->getMessage();
        }

        $duration = (int) ((microtime(true) - $start) * 1000);
        $this->logAction(
            'SCENARIO:' . $action,
            is_array($payload['data'] ?? null) ? Mask::maskArray($payload['data']) : $payload['data'],
            $duration,
            $success,
            $errorMessage,
            $responseData
        );

        return $this->json([
            'success' => $success,
            'duration_ms' => $duration,
            'response' => $responseData,
            'error' => $errorMessage,
        ], $success ? 200 : 500);
    }

    /**
     * @param array<string, mixed> $catalog
     * @param array<string, mixed> $payload
     */
    private function runCreateCustomer(array $catalog, array $payload, KvStore $kv): mixed
    {
        $target = $this->findMethod($catalog, ['customer'], ['create', 'new', 'register']);
        if ($target === null) {
            throw new \RuntimeException('Método de criação de cliente não encontrado. Use o Explorer.');
        }

        $payload = $payload ?: [
            'name' => 'Cliente Playground',
            'email' => 'playground@example.com',
        ];

        $response = $this->invokeCatalogMethod($target, [$payload]);
        $id = $response['id'] ?? null;
        if (is_string($id)) {
            $kv->set('last_customer_id', $id);
        }

        return $response;
    }

    /**
     * @param array<string, mixed> $catalog
     * @param array<string, mixed> $payload
     */
    private function runListCustomers(array $catalog, array $payload): mixed
    {
        $target = $this->findMethod($catalog, ['customer'], ['list', 'getAll', 'find']);
        if ($target === null) {
            throw new \RuntimeException('Método de listagem de clientes não encontrado. Use o Explorer.');
        }

        $payload = $payload ?: ['limit' => 10];
        return $this->invokeCatalogMethod($target, [$payload]);
    }

    /**
     * @param array<string, mixed> $catalog
     * @param array<string, mixed> $payload
     */
    private function runCreatePayment(array $catalog, array $payload, KvStore $kv): mixed
    {
        $target = $this->findMethod($catalog, ['payment'], ['create', 'new']);
        if ($target === null) {
            throw new \RuntimeException('Método de criação de cobrança não encontrado. Use o Explorer.');
        }

        $customerId = $payload['customer'] ?? $kv->get('last_customer_id');
        if (!$customerId) {
            throw new \RuntimeException('Nenhum customer id encontrado. Crie um cliente primeiro.');
        }

        $payload = $payload ?: [
            'customer' => $customerId,
            'billingType' => 'BOLETO',
            'value' => 10.5,
            'dueDate' => date('Y-m-d', strtotime('+3 days')),
        ];

        $response = $this->invokeCatalogMethod($target, [$payload]);
        $id = $response['id'] ?? null;
        if (is_string($id)) {
            $kv->set('last_payment_id', $id);
        }

        return $response;
    }

    /**
     * @param array<string, mixed> $catalog
     * @param array<string, mixed> $payload
     */
    private function runListPayments(array $catalog, array $payload): mixed
    {
        $target = $this->findMethod($catalog, ['payment'], ['list', 'getAll', 'find']);
        if ($target === null) {
            throw new \RuntimeException('Método de listagem de cobranças não encontrado. Use o Explorer.');
        }

        $payload = $payload ?: ['limit' => 10];
        return $this->invokeCatalogMethod($target, [$payload]);
    }

    /**
     * @param array<string, mixed> $catalog
     * @param array<string, mixed> $payload
     */
    private function runCancelPayment(array $catalog, array $payload, KvStore $kv): mixed
    {
        $target = $this->findMethod($catalog, ['payment'], ['cancel', 'delete', 'remove']);
        if ($target === null) {
            throw new \RuntimeException('Método de cancelamento não encontrado. Use o Explorer.');
        }

        $paymentId = $payload['id'] ?? $kv->get('last_payment_id');
        if (!$paymentId) {
            throw new \RuntimeException('Nenhum payment id encontrado. Crie uma cobrança primeiro.');
        }

        $args = $payload ?: ['id' => $paymentId];
        return $this->invokeCatalogMethod($target, [$args]);
    }

    /**
     * @param array{class: string, method: string} $target
     * @param array<int, mixed> $args
     */
    private function invokeCatalogMethod(array $target, array $args): mixed
    {
        $instance = $this->resolveService($target['class']);
        $reflection = new \ReflectionMethod($target['class'], $target['method']);

        return $reflection->invokeArgs($instance, $args);
    }

    /**
     * @param array<string, mixed> $catalog
     * @param string[] $serviceKeywords
     * @param string[] $methodKeywords
     * @return array{class: string, method: string}|null
     */
    private function findMethod(array $catalog, array $serviceKeywords, array $methodKeywords): ?array
    {
        foreach ($catalog['services'] ?? [] as $serviceClass) {
            $serviceName = strtolower($serviceClass);
            foreach ($serviceKeywords as $keyword) {
                if (!str_contains($serviceName, $keyword)) {
                    continue 2;
                }
            }

            $methods = $catalog['methods'][$serviceClass] ?? [];
            foreach ($methods as $method) {
                $methodName = strtolower($method['name']);
                foreach ($methodKeywords as $keyword) {
                    if (str_contains($methodName, strtolower($keyword))) {
                        return ['class' => $serviceClass, 'method' => $method['name']];
                    }
                }
            }
        }

        return null;
    }

    private function resolveService(string $class): object
    {
        $sdk = $this->bootstrap->sdk();
        $short = (new \ReflectionClass($class))->getShortName();
        $property = lcfirst(str_replace('Service', '', $short));

        if (property_exists($sdk, $property)) {
            return $sdk->{$property};
        }

        return new $class($this->bootstrap->client());
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
