<?php

declare(strict_types=1);

namespace App\SpaceTrader;

use Symfony\Contracts\HttpClient\HttpClientInterface;

class APIClient
{
    public function __construct(
        #[\SensitiveParameter] private readonly string $spaceTraderToken,
        private readonly HttpClientInterface $client,
    ) {
    }

    #region Agent
    /**
     * @return array{agent: array, contract: array, faction: array, ship: array, token: string}
     */
    public function registerAgent(string $symbol, string $faction = 'COSMIC'): array
    {
        $data = [
            'symbol' => $symbol,
            'faction' => $faction,
        ];

        $options = $this->getAccountRequestOptions();
        $options['body'] = json_encode($data);

        $response = $this->makeRequest('POST', 'https://api.spacetraders.io/v2/register', $options);

        return $response['data'];
    }

    /**
     * @return array{accountId: string, symbol: string, headquarters: string, credits: int, startingFaction: string, shipCount: int}
     */
    public function loadAgent(string $token): array
    {
        $options = $this->getAgentRequestOptions($token);
        $response = $this->makeRequest('GET', 'https://api.spacetraders.io/v2/my/agent', $options);

        return $response['data'];
    }
    #endregion

    #region Contract
    /**
     * @return array<int, array{id: string, factionString: string, type: string, terms: array, accepted: bool, fulfilled: bool, expiration: string, deadlineToAccept: string}>
     */
    public function loadContracts(string $token): array
    {
        $options = $this->getAgentRequestOptions($token);
        $response = $this->makeRequest('GET', 'https://api.spacetraders.io/v2/my/contracts', $options);

        return $response['data'];
    }

    public function acceptContract(string $token, string $contract): void
    {
        $options = $this->getAgentRequestOptions($token);
        $this->makeRequest('POST', "https://api.spacetraders.io/v2/my/contracts/$contract/accept", $options);
    }
    #endregion

    #region Ship
    /**
     * @return array<int, array{symbol: string, nav: array, crew: array, fuel: array, cooldown: array, frame: array, reactor: array, engine: array, modules: array, mounts: array, registration: array, cargo: array}>
     */
    public function loadShips(string $token): array
    {
        $options = $this->getAgentRequestOptions($token);
        $response = $this->makeRequest('GET', 'https://api.spacetraders.io/v2/my/ships', $options);

        return $response['data'];
    }

    public function dockShip(string $token, string $symbol): void
    {
        $options = $this->getAgentRequestOptions($token);
        $this->makeRequest('POST', "https://api.spacetraders.io/v2/my/ships/$symbol/dock", $options);
    }

    public function orbitShip(string $token, string $symbol): void
    {
        $options = $this->getAgentRequestOptions($token);
        $this->makeRequest('POST', "https://api.spacetraders.io/v2/my/ships/$symbol/orbit", $options);
    }
    #endregion

    #region Navigation
    /**
     * @return array{symbol: string, sectorSymbol: string, type: string, x: int, y: int, waypoints: array, factions: array}
     */
    public function loadSystem(string $symbol): array
    {
        $options = $this->getAccountRequestOptions();
        $response = $this->makeRequest('GET', "https://api.spacetraders.io/v2/systems/$symbol", $options);

        return $response['data'];
    }
    #endregion

    private function getAccountRequestOptions(): array
    {
        return [
            'auth_bearer' => $this->spaceTraderToken,
            'headers' => [
                'Content-Type' => 'application/json',
            ],
        ];
    }

    private function getAgentRequestOptions(string $token): array
    {
        return [
            'auth_bearer' => $token,
            'headers' => [
                'Content-Type' => 'application/json',
            ],
        ];
    }

    /**
     * @return array{data: array, meta?: array}
     */
    private function makeRequest(string $method, string $url, array $options = []): array
    {
        $response = $this->client->request($method, $url, $options);

        return json_decode($response->getContent(), true);
    }
}
