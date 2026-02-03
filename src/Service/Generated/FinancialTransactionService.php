<?php

declare(strict_types=1);

namespace Asaas\Sdk\Service\Generated;

use Asaas\Sdk\Service\AbstractService;

class FinancialTransactionService extends AbstractService
{
    /**
     * @param array<string, string> $pathParams
     * @param array<string, mixed> $query
     * @param array<string, string> $headers
     * @param array<string, mixed>|null $payload
     */
    public function retrieveExtract(
        array $pathParams = [],
        array $query = [],
        array $headers = [],
        ?array $payload = null
    ): mixed {
        return $this->request(
            'GET',
            '/v3/financialTransactions',
            $pathParams,
            $query,
            $headers,
            $payload,
            false,
            false
        );
    }

}
