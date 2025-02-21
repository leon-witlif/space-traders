<?php

declare(strict_types=1);

namespace App\SpaceTrader;

use App\SpaceTrader\Struct\Agent;

class AgentApi
{
    public function __construct(
        private readonly ApiClient $apiClient,
    ) {
    }

    /**
     * @return array{agent: array, contract: array, faction: array, ship: array, token: string}
     */
    public function registerAgent(string $symbol, string $faction = 'COSMIC'): array
    {
        $data = [
            'symbol' => $symbol,
            'faction' => $faction,
        ];

        $response = $this->apiClient->makeAccountRequest('POST', 'https://api.spacetraders.io/v2/register', ['body' => json_encode($data)]);

        return $response['data'];
    }

    public function loadAgent(string $token): Agent
    {
        $response = $this->apiClient->makeAgentRequest('GET', 'https://api.spacetraders.io/v2/my/agent', $token);

        return new Agent(...$response['data']);
    }
}
