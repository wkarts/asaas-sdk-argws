<?php

declare(strict_types=1);

namespace Asaas\Sdk\Service;

use Asaas\Sdk\Http\Client;
use Asaas\Sdk\Service\Generated\ChargebackService as GeneratedChargebackService;

/**
 * Camada "não-gerada" para manter compatibilidade e permitir extensões futuras,
 * enquanto os serviços em Service\Generated são final (gerados automaticamente).
 *
 * @mixin GeneratedChargebackService
 */
final class ChargebackService extends AbstractService
{
    private GeneratedChargebackService $generated;

    public function __construct(Client $client)
    {
        parent::__construct($client);
        $this->generated = new GeneratedChargebackService($client);
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
