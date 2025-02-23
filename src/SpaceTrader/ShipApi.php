<?php

declare(strict_types=1);

namespace App\SpaceTrader;

use App\SpaceTrader\Struct\Ship;

class ShipApi
{
    public function __construct(private readonly ApiClient $apiClient)
    {
    }

    /**
     * @return array<Ship>
     */
    public function list(string $token): array
    {
        $response = $this->apiClient->makeAgentRequest('GET', '/my/ships', $token);

        return array_map(fn (array $ship) => Ship::fromResponse($ship), $response['data']);
    }

    public function get(string $token, string $shipSymbol): Ship
    {
        $response = $this->apiClient->makeAgentRequest('GET', "/my/ships/$shipSymbol", $token);

        return Ship::fromResponse($response['data']);
    }

    public function orbit(string $token, string $shipSymbol): void
    {
        $this->apiClient->makeAgentRequest('POST', "/my/ships/$shipSymbol/orbit", $token);
    }

    public function dock(string $token, string $shipSymbol): void
    {
        $this->apiClient->makeAgentRequest('POST', "/my/ships/$shipSymbol/dock", $token);
    }

    public function extract(string $token, string $shipSymbol): void
    {
        $this->apiClient->makeAgentRequest('POST', "/my/ships/$shipSymbol/extract", $token);
    }

    public function jettison(string $token, string $shipSymbol, string $symbol, int $units): void
    {
        $data = [
            'symbol' => $symbol,
            'units' => $units,
        ];

        $this->apiClient->makeAgentRequest('POST', "/my/ships/$shipSymbol/jettison", $token, ['body' => json_encode($data)]);
    }

    public function navigate(string $token, string $shipSymbol, string $waypointSymbol): void
    {
        $data = [
            'waypointSymbol' => $waypointSymbol,
        ];

        $this->apiClient->makeAgentRequest('POST', "/my/ships/$shipSymbol/navigate", $token, ['body' => json_encode($data)]);
    }

    public function sell(string $token, string $shipSymbol, string $symbol, int $units): void
    {
        $data = [
            'symbol' => $symbol,
            'units' => $units,
        ];

        $this->apiClient->makeAgentRequest('POST', "/my/ships/$shipSymbol/sell", $token, ['body' => json_encode($data)]);
    }

    public function refuel(string $token, string $shipSymbol): void
    {
        $this->apiClient->makeAgentRequest('POST', "/my/ships/$shipSymbol/refuel", $token);
    }
}
