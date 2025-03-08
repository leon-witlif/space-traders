<?php

declare(strict_types=1);

namespace App\Contract;

use App\SpaceTrader\AgentApi;
use App\SpaceTrader\ContractApi;
use App\SpaceTrader\ShipApi;
use App\SpaceTrader\SystemApi;

class ContractFactory
{
    public function __construct(
        private readonly AgentApi $agentApi,
        private readonly ContractApi $contractApi,
        private readonly ShipApi $shipApi,
        private readonly SystemApi $systemApi,
    ) {
    }

    public function createProcurementContract(string $agentToken, string $contractId, string $shipSymbol): Procurement
    {
        return new Procurement($this->agentApi, $this->contractApi, $this->shipApi, $this->systemApi, ...func_get_args());
    }
}
