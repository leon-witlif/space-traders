<?php

declare(strict_types=1);

namespace App\SpaceTrader\Endpoint;

use App\SpaceTrader\ApiClient;
use App\SpaceTrader\ApiEndpoint;
use App\SpaceTrader\Struct\Agent;

class AgentApi implements ApiEndpoint
{
    public function __construct(private readonly ApiClient $apiClient)
    {
    }

    public function get(string $token, bool $disableCache = false): Agent
    {
        if (!$disableCache) {
            $this->apiClient->prepareRequestCache("agent-$token");
        }

        $response = $this->apiClient->makeAgentRequest('GET', '/my/agent', $token);

        return Agent::fromResponse($response['data']);
    }
}
