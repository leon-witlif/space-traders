<?php

declare(strict_types=1);

namespace App\SpaceTrader;

use App\SpaceTrader\Struct\Contract;

class ContractApi
{
    public function __construct(private readonly ApiClient $apiClient)
    {
    }

    /**
     * @return array<Contract>
     */
    public function list(string $token): array
    {
        $response = $this->apiClient->makeAgentRequest('GET', '/my/contracts', $token);

        return array_map(fn (array $contract) => Contract::fromResponse($contract), $response['data']);
    }

    public function get(string $token, string $contractId): Contract
    {
        $response = $this->apiClient->makeAgentRequest('GET', "/my/contracts/$contractId", $token);

        return Contract::fromResponse($response['data']);
    }

    public function accept(string $token, string $contractId): void
    {
        $this->apiClient->makeAgentRequest('POST', "/my/contracts/$contractId/accept", $token);
    }

    public function deliver(string $token, string $contractId, string $shipSymbol, string $tradeSymbol, int $units): void
    {
        $data = [
            'shipSymbol' => $shipSymbol,
            'tradeSymbol' => $tradeSymbol,
            'units' => $units,
        ];

        $this->apiClient->makeAgentRequest('POST', "/my/contracts/$contractId/deliver", $token, ['body' => json_encode($data)]);
    }

    public function fulfill(string $token, string $contractId): void
    {
        $this->apiClient->makeAgentRequest('POST', "/my/contracts/$contractId/fulfill", $token);
    }
}
