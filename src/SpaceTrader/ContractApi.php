<?php

declare(strict_types=1);

namespace App\SpaceTrader;

use App\SpaceTrader\Struct\Contract;

class ContractApi
{
    public function __construct(
        private readonly ApiClient $apiClient,
    ) {
    }

    /**
     * @return array<Contract>
     */
    public function loadContracts(string $token): array
    {
        $response = $this->apiClient->makeAgentRequest('GET', 'https://api.spacetraders.io/v2/my/contracts', $token);

        return array_map(fn (array $contract) => new Contract(...$contract), $response['data']);
    }

    public function loadContract(string $token, string $contractId): Contract
    {
        $response = $this->apiClient->makeAgentRequest('GET', "https://api.spacetraders.io/v2/my/contracts/$contractId", $token);

        return new Contract(...$response['data']);
    }

    public function acceptContract(string $token, string $contractId): void
    {
        $this->apiClient->makeAgentRequest('POST', "https://api.spacetraders.io/v2/my/contracts/$contractId/accept", $token);
    }

    public function deliverCargo(string $token, string $contractId, string $shipSymbol, string $cargoSymbol, int $units): void
    {
        $data = [
            'shipSymbol' => $shipSymbol,
            'tradeSymbol' => $cargoSymbol,
            'units' => $units,
        ];

        $this->apiClient->makeAgentRequest('POST', "https://api.spacetraders.io/v2/my/contracts/$contractId/deliver", $token, ['body' => json_encode($data)]);
    }

    public function fulfillContract(string $token, string $contractId): void
    {
        $this->apiClient->makeAgentRequest('POST', "https://api.spacetraders.io/v2/my/contracts/$contractId/fulfill", $token);
    }
}
