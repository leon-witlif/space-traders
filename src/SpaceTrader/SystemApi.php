<?php

declare(strict_types=1);

namespace App\SpaceTrader;

use App\SpaceTrader\Struct\Market;
use App\SpaceTrader\Struct\System;
use App\SpaceTrader\Struct\Waypoint;

class SystemApi
{
    public function __construct(private readonly ApiClient $apiClient)
    {
    }

    public function get(string $systemSymbol): System
    {
        $response = $this->apiClient->makeAccountRequest('GET', "/systems/$systemSymbol");

        return System::fromResponse($response['data']);
    }

    public function waypoint(string $systemSymbol, string $waypointSymbol): Waypoint
    {
        $response = $this->apiClient->makeAccountRequest('GET', "/systems/$systemSymbol/waypoints/$waypointSymbol");

        return Waypoint::fromResponse($response['data']);
    }

    public function market(string $systemSymbol, string $waypointSymbol): Market
    {
        $response = $this->apiClient->makeAccountRequest('GET', "/systems/$systemSymbol/waypoints/$waypointSymbol/market");

        return Market::fromResponse($response['data']);
    }
}
