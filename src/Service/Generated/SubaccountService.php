<?php

declare(strict_types=1);

namespace Asaas\Sdk\Service\Generated;

use Asaas\Sdk\Service\AbstractService;

class SubaccountService extends AbstractService
{
    /**
     * @param array<string, string> $pathParams
     * @param array<string, mixed> $query
     * @param array<string, string> $headers
     * @param array<string, mixed>|null $payload
     */
    public function listSubaccounts(
        array $pathParams = [],
        array $query = [],
        array $headers = [],
        ?array $payload = null
    ): mixed {
        return $this->request(
            'GET',
            '/v3/accounts',
            $pathParams,
            $query,
            $headers,
            $payload,
            false,
            false
        );
    }

    /**
     * @param array<string, string> $pathParams
     * @param array<string, mixed> $query
     * @param array<string, string> $headers
     * @param array<string, mixed>|null $payload
     */
    public function createSubaccount(
        array $pathParams = [],
        array $query = [],
        array $headers = [],
        ?array $payload = null
    ): mixed {
        return $this->request(
            'POST',
            '/v3/accounts',
            $pathParams,
            $query,
            $headers,
            $payload,
            false,
            false
        );
    }

    /**
     * @param array<string, string> $pathParams
     * @param array<string, mixed> $query
     * @param array<string, string> $headers
     * @param array<string, mixed>|null $payload
     */
    public function createApiKeyForSubaccount(
        array $pathParams = [],
        array $query = [],
        array $headers = [],
        ?array $payload = null
    ): mixed {
        return $this->request(
            'POST',
            '/v3/accounts/{id}/accessTokens',
            $pathParams,
            $query,
            $headers,
            $payload,
            false,
            false
        );
    }

    /**
     * @param array<string, string> $pathParams
     * @param array<string, mixed> $query
     * @param array<string, string> $headers
     * @param array<string, mixed>|null $payload
     */
    public function listApiKeysForSubaccount(
        array $pathParams = [],
        array $query = [],
        array $headers = [],
        ?array $payload = null
    ): mixed {
        return $this->request(
            'GET',
            '/v3/accounts/{id}/accessTokens',
            $pathParams,
            $query,
            $headers,
            $payload,
            false,
            false
        );
    }

    /**
     * @param array<string, string> $pathParams
     * @param array<string, mixed> $query
     * @param array<string, string> $headers
     * @param array<string, mixed>|null $payload
     */
    public function retrieveASingleSubaccount(
        array $pathParams = [],
        array $query = [],
        array $headers = [],
        ?array $payload = null
    ): mixed {
        return $this->request(
            'GET',
            '/v3/accounts/{id}',
            $pathParams,
            $query,
            $headers,
            $payload,
            false,
            false
        );
    }

    /**
     * @param array<string, string> $pathParams
     * @param array<string, mixed> $query
     * @param array<string, string> $headers
     * @param array<string, mixed>|null $payload
     */
    public function updateApiKeyForASubaccount(
        array $pathParams = [],
        array $query = [],
        array $headers = [],
        ?array $payload = null
    ): mixed {
        return $this->request(
            'PUT',
            '/v3/accounts/{id}/accessTokens/{accessTokenId}',
            $pathParams,
            $query,
            $headers,
            $payload,
            false,
            false
        );
    }

    /**
     * @param array<string, string> $pathParams
     * @param array<string, mixed> $query
     * @param array<string, string> $headers
     * @param array<string, mixed>|null $payload
     */
    public function deleteApiKeyForASubaccount(
        array $pathParams = [],
        array $query = [],
        array $headers = [],
        ?array $payload = null
    ): mixed {
        return $this->request(
            'DELETE',
            '/v3/accounts/{id}/accessTokens/{accessTokenId}',
            $pathParams,
            $query,
            $headers,
            $payload,
            false,
            false
        );
    }

}
