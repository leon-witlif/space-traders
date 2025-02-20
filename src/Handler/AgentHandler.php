<?php

declare(strict_types=1);

namespace App\Handler;

use App\SpaceTrader\Agent;
use App\SpaceTrader\APIClient as SpaceTraderAPI;
use App\SpaceTrader\Contract;
use App\Storage\AgentStorage;

class AgentHandler
{
    public function __construct(
        private readonly AgentStorage $storage,
        private readonly SpaceTraderAPI $spaceTraderApi,
    ) {
    }

    public function register(string $symbol, string $faction): void
    {
        $response = $this->spaceTraderApi->registerAgent($symbol, $faction);

        $token = $response['data']['token'];

        $this->storage->addAgent($symbol, $token);
    }

    /**
     * @return array{agent: Agent, contracts: Contract[]}
     */
    public function load(string $token): array
    {
        return [
            'agent' => $this->spaceTraderApi->loadAgent($token),
            'ships' => $this->spaceTraderApi->loadShips($token),
            'contracts' => $this->spaceTraderApi->loadContracts($token),
        ];
    }
}
