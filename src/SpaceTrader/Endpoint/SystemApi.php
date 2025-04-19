<?php

declare(strict_types=1);

namespace App\SpaceTrader\Endpoint;

use App\SpaceTrader\ApiClient;
use App\SpaceTrader\ApiEndpoint;
use App\SpaceTrader\Struct\Market;
use App\SpaceTrader\Struct\Shipyard;
use App\SpaceTrader\Struct\System;
use App\SpaceTrader\Struct\Waypoint;

class SystemApi implements ApiEndpoint
{
    public function __construct(private readonly ApiClient $apiClient)
    {
    }

    public function get(string $systemSymbol, bool $disableCache = false): System
    {
        if (!$disableCache) {
            $this->apiClient->prepareRequestCache("system-$systemSymbol");
        }

        $response = $this->apiClient->makeAccountRequest('GET', "/systems/$systemSymbol");

        return System::fromResponse($response['data']);
    }

    public function waypoint(string $systemSymbol, string $waypointSymbol, bool $disableCache = false): Waypoint
    {
        if (!$disableCache) {
            $this->apiClient->prepareRequestCache("system-$systemSymbol-waypoint-$waypointSymbol");
        }

        $response = $this->apiClient->makeAccountRequest('GET', "/systems/$systemSymbol/waypoints/$waypointSymbol");

        return Waypoint::fromResponse($response['data']);
    }

    public function market(string $systemSymbol, string $waypointSymbol, bool $disableCache = false): Market
    {
        if (!$disableCache) {
            $this->apiClient->prepareRequestCache("system-$systemSymbol-market-$waypointSymbol");
        }

        $response = $this->apiClient->makeAccountRequest('GET', "/systems/$systemSymbol/waypoints/$waypointSymbol/market");

        return Market::fromResponse($response['data']);
    }

    public function shipyard(string $systemSymbol, string $waypointSymbol, bool $disableCache = false): Shipyard
    {
        if (!$disableCache) {
            $this->apiClient->prepareRequestCache("system-$waypointSymbol-shipyard-$waypointSymbol");
        }

        $response = $this->apiClient->makeAccountRequest('GET', "/systems/$systemSymbol/waypoints/$waypointSymbol/shipyard");

        return Shipyard::fromResponse($response['data']);
    }
}
