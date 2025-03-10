<?php

declare(strict_types=1);

namespace App\Contract;

use App\SpaceTrader\ApiRegistry;

class ContractFactory
{
    public function __construct(private readonly ApiRegistry $apiRegistry)
    {
    }

    public function createProcurementContract(string $agentToken, string $contractId, string $shipSymbol): Procurement
    {
        return new Procurement($this->apiRegistry, ...func_get_args());
    }
}
