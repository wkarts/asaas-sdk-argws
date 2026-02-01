<?php

declare(strict_types=1);

namespace Asaas\Sdk\Exception;

final class ApiException extends \RuntimeException
{
    /** @var array<int, array<string, mixed>> */
    private array $errors;

    /**
     * @param array<int, array{code?: string|null, description?: string|null}|array<string, mixed>> $errors
     */
    public function __construct(
        string $message,
        private int $statusCode,
        private string $rawBody,
        array $errors = []
    ) {
        parent::__construct($message, $statusCode);
        $this->errors = $errors;
    }

    public static function fromResponse(int $statusCode, string $rawBody): self
    {
        $errors = [];
        $message = 'Erro na API Asaas.';

        $decoded = json_decode($rawBody, true);

        if (is_array($decoded)) {
            if (isset($decoded['errors']) && is_array($decoded['errors'])) {
                $errors = $decoded['errors'];
                $first = $errors[0]['description'] ?? null;
                if (is_string($first)) {
                    $message = $first;
                }
            } elseif (isset($decoded['message']) && is_string($decoded['message'])) {
                $message = $decoded['message'];
            }
        }

        return new self($message, $statusCode, $rawBody, $errors);
    }

    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    public function getRawBody(): string
    {
        return $this->rawBody;
    }

    /** @return array<int, array<string, mixed>> */
    public function getErrors(): array
    {
        return $this->errors;
    }
}
