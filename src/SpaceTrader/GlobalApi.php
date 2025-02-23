<?php

declare(strict_types=1);

namespace App\SpaceTrader;

use App\SpaceTrader\Struct\Agent;

class GlobalApi
{
    public function __construct(private readonly ApiClient $apiClient)
    {
    }

    /**
     * @return array{agent: Agent, contract: array, faction: array, ship: array, token: string}
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

        return $content;
    }
}
