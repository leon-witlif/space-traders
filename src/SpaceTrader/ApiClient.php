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
     * @param array<string, mixed> $data
     * @return array{data: array<int|string, mixed>, meta?: array{total: int, page: int, limit: int}}
     */
    public function makeAccountRequest(string $method, string $path, array $data = []): array
    {
        $options = $this->getRequestOptions($this->spaceTraderToken) + $data;

        return $this->makeRequest($method, $path, $options);
    }

    /**
     * @param array<string, mixed> $data
     * @return array{data: array<int|string, mixed>, meta?: array{total: int, page: int, limit: int}}
     */
    public function makeAgentRequest(string $method, string $path, string $token, array $data = []): array
    {
        $options = $this->getRequestOptions($token) + $data;

        return $this->makeRequest($method, $path, $options);
    }

    /**
     * @return array{auth_bearer: string, headers: array{Content-Type: string}}
     */
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
     * @phpstan-param array<string, mixed> $options
     * @phpstan-return array{data: array<int|string, mixed>, meta?: array{total: int, page: int, limit: int}}
     */
    private function makeRequest(string $method, string $path, array $options = []): array
    {
        $response = $this->client->request($method, self::API.$path, $options);

        return json_decode($response->getContent(), true);
    }
}
