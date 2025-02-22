<?php

declare(strict_types=1);

namespace App\Storage;

class ContractStorage extends Storage
{
    public function __construct(
        private readonly string $projectDir,
    ) {
        parent::__construct($this->projectDir.'/var/space-trader/', 'contracts.json');
    }

    public function addContract(string $token, string $contractId, array $data): void
    {
        $this->data[] = [
            'token' => $token,
            'contract' => $contractId,
            'data' => $data,
        ];

        $this->save();
    }

    public function updateContract(string $contractId, array $data): void
    {
        $contractIndex = array_find_key($this->getContracts(), fn (array $contract) => $contract['contract'] === $contractId);

        $this->data[$contractIndex]['data'] = $data;

        $this->save();
    }

    public function removeContract(string $contractId): void
    {
        $this->data = array_values(array_filter($this->getContracts(), fn (array $contract) => $contract['contract'] !== $contractId));

        $this->save();
    }

    /**
     * @return array<int, array{token: string, contract: string, data: array}>
     */
    public function getContracts(): array
    {
        $this->load();

        return $this->data;
    }

    /**
     * @return array{token: string, contract: string, data: array}
     */
    public function getContract(string $contractId): array
    {
        return array_find($this->getContracts(), fn (array $contract) => $contract['contract'] === $contractId);
    }
}
