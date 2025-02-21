<?php

declare(strict_types=1);

namespace App\SpaceTrader;

use App\SpaceTrader\Struct\Ship;

class ShipApi
{
    public function __construct(
        private readonly ApiClient $apiClient,
    ) {
    }

    /**
     * @return array<Ship>
     */
    public function loadShips(string $token): array
    {
        $response = $this->apiClient->makeAgentRequest('GET', 'https://api.spacetraders.io/v2/my/ships', $token);

        return array_map(fn (array $ship) => new Ship(...$ship), $response['data']);
    }

    public function dockShip(string $token, string $symbol): void
    {
        $this->apiClient->makeAgentRequest('POST', "https://api.spacetraders.io/v2/my/ships/$symbol/dock", $token);
    }

    public function orbitShip(string $token, string $symbol): void
    {
        $this->apiClient->makeAgentRequest('POST', "https://api.spacetraders.io/v2/my/ships/$symbol/orbit", $token);
    }
}
