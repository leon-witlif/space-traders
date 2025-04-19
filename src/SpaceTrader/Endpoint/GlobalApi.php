<?php

declare(strict_types=1);

namespace App\SpaceTrader\Endpoint;

use App\SpaceTrader\ApiClient;
use App\SpaceTrader\ApiEndpoint;
use App\SpaceTrader\Struct\Agent;
use App\SpaceTrader\Struct\Contract;
use App\SpaceTrader\Struct\Faction;

class GlobalApi implements ApiEndpoint
{
    public function __construct(private readonly ApiClient $apiClient)
    {
    }

    /**
     * @return array{
     *     status: string,
     *     version: string,
     *     resetDate: string,
     *     description: string,
     *     stats: array<string, int>,
     *     leaderboards: array{
     *         mostCredits: array<int, array{agentSymbol: string, credits: int}>,
     *         mostSubmittedCharts: array<int, mixed>
     *     },
     *     serverResets: array{
     *         next: string,
     *         frequency: string
     *     },
     *     announcements: array<int, mixed>,
     *     links: array<int, mixed>
     * }
     */
    public function status(bool $disableCache = false): array
    {
        if (!$disableCache) {
            $this->apiClient->prepareRequestCache('global-status');
        }

        /** @phpstan-ignore-next-line Response body does not follow the typical structure */
        return $this->apiClient->makeAccountRequest('GET', '');
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
