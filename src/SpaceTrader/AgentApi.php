<?php

declare(strict_types=1);

namespace App\SpaceTrader;

use App\SpaceTrader\Struct\Agent;

class AgentApi
{
    public function __construct(private readonly ApiClient $apiClient)
    {
    }

    public function get(string $token): Agent
    {
        $response = $this->apiClient->makeAgentRequest('GET', '/my/agent', $token);

        return Agent::fromResponse($response['data']);
    }
}
