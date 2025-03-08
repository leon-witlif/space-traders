<?php

declare(strict_types=1);

namespace App\SpaceTrader;

use App\SpaceTrader\Struct\Agent;
use App\SpaceTrader\Struct\Contract;
use App\SpaceTrader\Struct\Faction;
use App\SpaceTrader\Struct\Ship;

class GlobalApi
{
    public function __construct(private readonly ApiClient $apiClient)
    {
    }

    /**
     * @return array{agent: Agent, contract: Contract, faction: Faction, token: string}
     */
    public function register(string $faction, string $symbol, string $email = ''): array
    {
        $data = [
            'faction' => $faction,
            'symbol' => $symbol,
            'email' => $email,
        ];

        $response = $this->apiClient->makeAccountRequest('POST', '/register', ['body' => json_encode($data)]);
        $content = $response['data'];

        $content['agent'] = Agent::fromResponse($content['agent']);
        $content['contract'] = Contract::fromResponse($content['contract']);
        $content['faction'] = Faction::fromResponse($content['faction']);

        return $content;
    }
}
