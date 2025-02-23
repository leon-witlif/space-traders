<?php

declare(strict_types=1);

namespace App\SpaceTrader;

use App\SpaceTrader\Struct\Cooldown;
use App\SpaceTrader\Struct\Ship;
use App\SpaceTrader\Struct\ShipCargo;
use App\SpaceTrader\Struct\ShipNav;

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

    /**
     * @return array{
     *     cooldown: Cooldown,
     *     extraction: array,
     *     cargo: ShipCargo,
     *     events: array
     * }
     */
    public function extract(string $token, string $shipSymbol): array
    {
        $response = $this->apiClient->makeAgentRequest('POST', "/my/ships/$shipSymbol/extract", $token);
        $content = $response['data'];

        $content['cargo'] = ShipCargo::fromResponse($content['cargo']);

        return $content;
    }

    public function jettison(string $token, string $shipSymbol, string $symbol, int $units): ShipCargo
    {
        $data = [
            'symbol' => $symbol,
            'units' => $units,
        ];

        $response = $this->apiClient->makeAgentRequest('POST', "/my/ships/$shipSymbol/jettison", $token, ['body' => json_encode($data)]);

        return ShipCargo::fromResponse($response['data']['cargo']);
    }

    /**
     * @return array{
     *     fuel: array,
     *     nav: ShipNav,
     *     events: array
     * }
     */
    public function navigate(string $token, string $shipSymbol, string $waypointSymbol): array
    {
        $data = [
            'waypointSymbol' => $waypointSymbol,
        ];

        $response = $this->apiClient->makeAgentRequest('POST', "/my/ships/$shipSymbol/navigate", $token, ['body' => json_encode($data)]);
        $content = $response['data'];

        $content['nav'] = ShipNav::fromResponse($content['nav']);

        return $content;
    }

    public function nav(string $token, string $shipSymbol, string $flightMode): void
    {
        $data = [
            'flightMode' => $flightMode,
        ];

        $this->apiClient->makeAgentRequest('PATCH', "/my/ships/$shipSymbol/nav", $token, ['body' => json_encode($data)]);
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

    public function negotiate(string $token, string $shipSymbol): void
    {
        $this->apiClient->makeAgentRequest('POST', "/my/ships/$shipSymbol/negotiate/contract", $token);
    }
}
