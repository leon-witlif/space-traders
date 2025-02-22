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

    public function loadShip(string $token, string $symbol): Ship
    {
        $response = $this->apiClient->makeAgentRequest('GET', "https://api.spacetraders.io/v2/my/ships/$symbol", $token);

        return new Ship(...$response['data']);
    }

    public function dockShip(string $token, string $symbol): void
    {
        $this->apiClient->makeAgentRequest('POST', "https://api.spacetraders.io/v2/my/ships/$symbol/dock", $token);
    }

    public function orbitShip(string $token, string $symbol): void
    {
        $this->apiClient->makeAgentRequest('POST', "https://api.spacetraders.io/v2/my/ships/$symbol/orbit", $token);
    }

    public function navigateShip(string $token, string $symbol, string $waypoint): void
    {
        $data = [
            'waypointSymbol' => $waypoint,
        ];

        $this->apiClient->makeAgentRequest('POST', "https://api.spacetraders.io/v2/my/ships/$symbol/navigate", $token, ['body' => json_encode($data)]);
    }

    public function refuelShip(string $token, string $symbol): void
    {
        $this->apiClient->makeAgentRequest('POST', "https://api.spacetraders.io/v2/my/ships/$symbol/refuel", $token);
    }

    public function extract(string $token, string $symbol): void
    {
        $this->apiClient->makeAgentRequest('POST', "https://api.spacetraders.io/v2/my/ships/$symbol/extract", $token);
    }

    public function jettison(string $token, string $symbol, string $cargo, int $units): void
    {
        $data = [
            'symbol' => $cargo,
            'units' => $units,
        ];

        $this->apiClient->makeAgentRequest('POST', "https://api.spacetraders.io/v2/my/ships/$symbol/jettison", $token, ['body' => json_encode($data)]);
    }
}
