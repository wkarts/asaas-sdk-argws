<?php

declare(strict_types=1);

namespace Asaas\Sdk\Http;

use Asaas\Sdk\Exception\ApiException;
use Asaas\Sdk\Exception\TransportException;
use Asaas\Sdk\Util\Query;
use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\HandlerStack;
use Psr\Log\LoggerInterface;

final class Client
{
    private GuzzleClient $client;
    private string $apiKey;
    private string $appName;
    private Environment $environment;

    public function __construct(
        string $apiKey,
        Environment $environment,
        string $appName,
        float $timeout = 30.0,
        float $connectTimeout = 10.0,
        ?LoggerInterface $logger = null,
        ?GuzzleClient $client = null
    ) {
        $this->apiKey = $apiKey;
        $this->environment = $environment;
        $this->appName = $appName;

        $stack = HandlerStack::create();
        $stack->push(RetryMiddleware::create());

        if ($client !== null) {
            $this->client = $client;
            return;
        }

        $options = [
            'base_uri' => $environment->value,
            'timeout' => $timeout,
            'connect_timeout' => $connectTimeout,
            'handler' => $stack,
        ];

        if ($logger !== null) {
            $options['logger'] = $logger;
        }

        $this->client = new GuzzleClient($options);
    }

    public function setApiKey(string $apiKey): void
    {
        $this->apiKey = $apiKey;
    }

    public function setEnvironment(Environment $environment): void
    {
        $this->environment = $environment;
        $this->client = new GuzzleClient([
            'base_uri' => $this->environment->value,
        ] + $this->client->getConfig());
    }

    /**
     * @param array<string, mixed> $query
     * @param array<string, string> $headers
     * @param array<string, mixed>|null $payload
     */
    public function request(
        string $method,
        string $path,
        array $query = [],
        array $headers = [],
        ?array $payload = null,
        bool $multipart = false,
        bool $expectsBinary = false
    ): mixed {
        $defaultHeaders = [
            'Accept' => 'application/json',
            'User-Agent' => $this->appName,
            'access_token' => $this->apiKey,
        ];

        if (!$multipart) {
            $defaultHeaders['Content-Type'] = 'application/json';
        }

        $options = [
            'headers' => $headers + $defaultHeaders,
            'query' => Query::normalize($query),
        ];

        if ($payload !== null) {
            if ($multipart) {
                $options['multipart'] = $this->formatMultipart($payload);
            } else {
                $options['json'] = $payload;
            }
        }

        $options['http_errors'] = false;

        try {
            $response = $this->client->request($method, $path, $options);
        } catch (\Throwable $exception) {
            throw new TransportException('Erro de transporte ao chamar API Asaas.', 0, $exception);
        }

        $status = $response->getStatusCode();
        $body = (string) $response->getBody();

        if ($status >= 400) {
            throw ApiException::fromResponse($status, $body);
        }

        if ($expectsBinary) {
            return $body;
        }

        if ($body === '') {
            return [];
        }

        $decoded = json_decode($body, true);

        return is_array($decoded) ? $decoded : [];
    }

    /**
     * @param array<string, mixed> $payload
     * @return array<int, array<string, mixed>>
     */
    private function formatMultipart(array $payload): array
    {
        $parts = [];

        foreach ($payload as $name => $contents) {
            $part = ['name' => (string) $name];

            if (is_resource($contents)) {
                $part['contents'] = $contents;
            } else {
                $part['contents'] = (string) $contents;
            }

            $parts[] = $part;
        }

        return $parts;
    }
}
