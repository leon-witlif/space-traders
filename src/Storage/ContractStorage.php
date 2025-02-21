<?php

declare(strict_types=1);

namespace App\Storage;

use App\Contract\Procurement;

class ContractStorage extends Storage
{
    public function __construct(
        private readonly string $projectDir,
    ) {
        parent::__construct($this->projectDir.'/var/space-trader/', 'contracts.json');
    }

    public function addContract(string $token, Procurement $contract): void
    {
        $this->data[] = [
            'token' => $token,
            'contract' => $contract->contract->id,
            'agent' => $contract->agent->symbol,
            'ship' => $contract->ship->symbol,
            'action' => $contract->getAction(),
        ];

        $this->save();
    }

    public function getContract(string $contractId): array
    {
        return array_find($this->data, fn (array $contract) => $contract['contract'] === $contractId);
    }
}
