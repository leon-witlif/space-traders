<?php

declare(strict_types=1);

namespace App\SpaceTrader;

use Symfony\Contracts\HttpClient\HttpClientInterface;

final class ApiClient
{
    private const string API = 'https://api.spacetraders.io/v2';

    public function __construct(
        #[\SensitiveParameter] private readonly string $spaceTraderToken,
        private readonly HttpClientInterface $client,
    ) {
    }

    /**
     * @return array{data: array, meta?: array}
     */
    public function makeAccountRequest(string $method, string $path, array $data = []): array
    {
        $options = $this->getRequestOptions($this->spaceTraderToken) + $data;

        return $this->makeRequest($method, $path, $options);
    }

    /**
     * @return array{data: array, meta?: array}
     */
    public function makeAgentRequest(string $method, string $path, string $token, array $data = []): array
    {
        $options = $this->getRequestOptions($token) + $data;

        return $this->makeRequest($method, $path, $options);
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
    private function makeRequest(string $method, string $path, array $options = []): array
    {
        $response = $this->client->request($method, self::API.$path, $options);

        return json_decode($response->getContent(), true);
    }
}
