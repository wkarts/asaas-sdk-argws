<?php

declare(strict_types=1);

namespace Asaas\Sdk\Service;

use Asaas\Sdk\Http\Client;
use Asaas\Sdk\Service\Generated\CreditBureauReportService as GeneratedCreditBureauReportService;

/**
 * Camada "não-gerada" para manter compatibilidade e permitir extensões futuras,
 * enquanto os serviços em Service\Generated são final (gerados automaticamente).
 *
 * @mixin GeneratedCreditBureauReportService
 */
final class CreditBureauReportService extends AbstractService
{
    private GeneratedCreditBureauReportService $generated;

    public function __construct(Client $client)
    {
        parent::__construct($client);
        $this->generated = new GeneratedCreditBureauReportService($client);
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
