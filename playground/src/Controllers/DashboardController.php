<?php

declare(strict_types=1);

namespace Playground\Controllers;

use Playground\Utils\ReflectionScanner;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final class DashboardController extends AbstractController
{
    public function index(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        return $this->render('dashboard', [
            'env' => strtolower($this->bootstrap->env('ASAAS_ENV', 'sandbox')),
        ]);
    }

    public function health(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $scanner = new ReflectionScanner($this->bootstrap->basePath());
        $catalog = $scanner->catalog();
        $start = microtime(true);

        $result = [
            'ok' => false,
            'duration_ms' => 0,
            'error' => null,
            'response' => null,
        ];

        try {
            $healthResponse = $this->runLightweightCall($catalog['services'] ?? []);
            $result['ok'] = true;
            $result['response'] = $healthResponse;
        } catch (\Throwable $exception) {
            $result['error'] = $exception->getMessage();
        }

        $result['duration_ms'] = (int) ((microtime(true) - $start) * 1000);

        return $this->json($result);
    }

    /**
     * @param string[] $services
     * @return mixed
     */
    private function runLightweightCall(array $services): mixed
    {
        foreach ($services as $serviceClass) {
            if (!class_exists($serviceClass)) {
                continue;
            }
            $reflection = new \ReflectionClass($serviceClass);
            foreach ($reflection->getMethods(\ReflectionMethod::IS_PUBLIC) as $method) {
                $methodName = $method->getName();
                if (!str_contains(strtolower($methodName), 'list')) {
                    continue;
                }

                $instance = $this->resolveService($serviceClass);
                $args = [];
                foreach ($method->getParameters() as $parameter) {
                    if ($parameter->getName() === 'limit') {
                        $args[] = 1;
                    } elseif ($parameter->isDefaultValueAvailable()) {
                        $args[] = $parameter->getDefaultValue();
                    } else {
                        $args[] = null;
                    }
                }

                return $method->invokeArgs($instance, $args);
            }
        }

        $client = $this->bootstrap->client();
        return $client->request('GET', '/customers', ['limit' => 1]);
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
}
