<?php

declare(strict_types=1);

namespace Asaas\Sdk\Service;

use Asaas\Sdk\Http\Client;
use Asaas\Sdk\Service\Generated\FinanceService as GeneratedFinanceService;

/**
 * Camada "não-gerada" para manter compatibilidade e permitir extensões futuras,
 * enquanto os serviços em Service\Generated são final (gerados automaticamente).
 *
 * @mixin GeneratedFinanceService
 */
final class FinanceService extends AbstractService
{
    private GeneratedFinanceService $generated;

    public function __construct(Client $client)
    {
        parent::__construct($client);
        $this->generated = new GeneratedFinanceService($client);
    }

    /**
     * @param string $name
     * @param array<int, mixed> $arguments
     */
    public function __call(string $name, array $arguments): mixed
    {
        /** @var mixed */
        return $this->generated->$name(...$arguments);
    }
}
