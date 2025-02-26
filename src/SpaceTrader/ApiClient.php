<?php

declare(strict_types=1);

namespace App\SpaceTrader;

use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

final class ApiClient
{
    private const string API = 'https://api.spacetraders.io/v2';

    private string $requestCacheKey;
    private int $requestCacheExpiresAfter;

    public function __construct(
        #[\SensitiveParameter] private readonly string $spaceTraderToken,
        private readonly HttpClientInterface $client,
        private readonly CacheInterface $spaceTraderPool,
    ) {
        $this->requestCacheKey = '';
        $this->requestCacheExpiresAfter = 0;
    }

    public function prepareRequestCache(string $key, int $expiresAfter = 1800): void
    {
        $this->requestCacheKey = $key;
        $this->requestCacheExpiresAfter = $expiresAfter;
    }

    public function clearRequestCache(string ...$keys): void
    {
        foreach ($keys as $key) {
            $this->spaceTraderPool->delete($key);
        }
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
        if ($this->requestCacheKey && $this->requestCacheExpiresAfter) {
            $content = $this->spaceTraderPool->get(
                $this->requestCacheKey,
                function (ItemInterface $item) use ($method, $path, $options) {
                    $item->expiresAfter($this->requestCacheExpiresAfter);

                    $response = $this->client->request($method, self::API.$path, $options);

                    return $response->getContent();
                }
            );
        } else {
            $response = $this->client->request($method, self::API.$path, $options);
            $content = $response->getContent();
        }

        $this->prepareRequestCache('', 0);

        return json_decode($content, true);
    }
}
