<?php

declare(strict_types=1);

namespace App\Handler;

use App\SpaceTrader\Agent;
use App\SpaceTrader\APIClient as SpaceTraderAPI;

class AgentHandler
{
    public function __construct(
        private readonly SpaceTraderAPI $spaceTraderApi,
    ) {
    }

    public function register(string $symbol, string $faction): string
    {
        $response = $this->spaceTraderApi->registerAgent($symbol, $faction);

        return $response['token'];
    }

    public function load(string $token): Agent
    {
        $response = $this->spaceTraderApi->loadAgent($token);

        return new Agent(...$response);
    }
}
