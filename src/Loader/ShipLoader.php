<?php

declare(strict_types=1);

namespace App\Loader;

use App\SpaceTrader\ShipApi;
use App\SpaceTrader\Struct\Ship;
use App\Storage\ContractStorage;

class ShipLoader
{
    public function __construct(
        private readonly AgentTokenProvider $agentTokenProvider,
        private readonly ShipApi $shipApi,
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

        return $this->shipApi->list($this->agentTokenProvider->getAgentToken(), $disableCache);
    }

    public function get(string $shipSymbol): Ship
    {
        $disableCache = false;

        if (array_find($this->contractStorage->list(), fn (array $contract) => $contract['shipSymbol'] === $shipSymbol)) {
            $disableCache = true;
        }

        return $this->shipApi->get($this->agentTokenProvider->getAgentToken(), $shipSymbol, $disableCache);
    }
}
