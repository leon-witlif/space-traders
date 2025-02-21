<?php

declare(strict_types=1);

namespace App\SpaceTrader;

use Symfony\Contracts\HttpClient\HttpClientInterface;

final class ApiClient
{
    public function __construct(
        #[\SensitiveParameter] private readonly string $spaceTraderToken,
        private readonly HttpClientInterface $client,
    ) {
    }

    /**
     * @return array{data: array, meta?: array}
     */
    public function makeAccountRequest(string $method, string $url, array $data = []): array
    {
        $options = $this->getRequestOptions($this->spaceTraderToken) + $data;

        return $this->makeRequest($method, $url, $options);
    }

    /**
     * @return array{data: array, meta?: array}
     */
    public function makeAgentRequest(string $method, string $url, string $token, array $data = []): array
    {
        $options = $this->getRequestOptions($token) + $data;

        return $this->makeRequest($method, $url, $options);
    }

    private function getRequestOptions(string $bearerToken): array
    {
        return [
            'auth_bearer' => $bearerToken,
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
