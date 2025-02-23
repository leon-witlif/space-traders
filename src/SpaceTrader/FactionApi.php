<?php

declare(strict_types=1);

namespace App\SpaceTrader;

use App\SpaceTrader\Struct\Faction;

class FactionApi
{
    public function __construct(private readonly ApiClient $apiClient)
    {
    }

    public function get(string $factionSymbol): Faction
    {
        $response = $this->apiClient->makeAccountRequest('GET', "/factions/$factionSymbol");

        return Faction::fromResponse($response['data']);
    }
}
