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

    public function registerAgent(string $symbol, string $faction = 'COSMIC'): array
    {
        $data = [
            'symbol' => $symbol,
            'faction' => $faction,
        ];

        $options = $this->getAccountRequestOptions();
        $options['body'] = json_encode($data);

        $response = $this->client->request('POST', 'https://api.spacetraders.io/v2/register', $options);
        $content = json_decode($response->getContent(), true);

        return $content;
    }

    public function loadAgent(string $token): Agent
    {
        $options = $this->getAgentRequestOptions($token);

        $response = $this->client->request('GET', 'https://api.spacetraders.io/v2/my/agent', $options);
        $content = json_decode($response->getContent(), true);

        return new Agent(...$content['data']);
    }

    /**
     * @return array<Contract>
     */
    public function loadContracts(string $token): array
    {
        $options = $this->getAgentRequestOptions($token);

        $response = $this->client->request('GET', 'https://api.spacetraders.io/v2/my/contracts', $options);
        $content = json_decode($response->getContent(), true);

        $contracts = [];

        foreach ($content['data'] as $contract) {
            $contracts[] = new Contract(...$contract);
        }

        return $contracts;
    }

    public function acceptContract(string $token, string $contract): void
    {
        $options = $this->getAgentRequestOptions($token);

        $response = $this->client->request('POST', "https://api.spacetraders.io/v2/my/contracts/$contract/accept", $options);
        // Wait until the request is finished
        $content = json_decode($response->getContent(), true);
    }

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
}
