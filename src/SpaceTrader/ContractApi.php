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

    public function acceptContract(string $token, string $contract): void
    {
        $this->apiClient->makeAgentRequest('POST', "https://api.spacetraders.io/v2/my/contracts/$contract/accept", $token);
    }
}
