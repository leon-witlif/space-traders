<?php

declare(strict_types=1);

namespace App\Loader;

use App\SpaceTrader\ApiRegistry;
use App\SpaceTrader\ApiShorthands;
use App\SpaceTrader\Struct\Ship;
use App\Storage\ContractStorage;

class ShipLoader
{
    use ApiShorthands;

    public function __construct(
        private readonly ApiRegistry $apiRegistry,
        private readonly AgentTokenProvider $agentTokenProvider,
        private readonly ContractStorage $contractStorage,
    ) {
    }

    /**
     * @return array<Ship>
     */
    public function list(): array
    {
        $disableCache = false;

        if (array_any($this->contractStorage->list(), fn (array $contract) => $contract['agentToken'] === $this->agentTokenProvider->getAgentToken())) {
            $disableCache = true;
        }

        return $this->getShipApi()->list($this->agentTokenProvider->getAgentToken(), $disableCache);
    }

    public function get(string $shipSymbol): Ship
    {
        $disableCache = false;

        if (array_find($this->contractStorage->list(), fn (array $contract) => $contract['shipSymbol'] === $shipSymbol)) {
            $disableCache = true;
        }

        return $this->getShipApi()->get($this->agentTokenProvider->getAgentToken(), $shipSymbol, $disableCache);
    }
}
