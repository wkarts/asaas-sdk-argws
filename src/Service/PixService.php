<?php

declare(strict_types=1);

namespace Asaas\Sdk\Service;

use Asaas\Sdk\Http\Client;
use Asaas\Sdk\Service\Generated\PixService as GeneratedPixService;

/**
 * Camada "não-gerada" para manter compatibilidade e permitir extensões futuras,
 * enquanto os serviços em Service\Generated são final (gerados automaticamente).
 *
 * @mixin GeneratedPixService
 */
final class PixService extends AbstractService
{
    private GeneratedPixService $generated;

    public function __construct(Client $client)
    {
        parent::__construct($client);
        $this->generated = new GeneratedPixService($client);
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
