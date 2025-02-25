<?php

declare(strict_types=1);

namespace App\Storage;

/**
 * @phpstan-extends Storage<array{agentToken: string, contractId: string, shipSymbol: string, data: array}>
 */
class ContractStorage extends Storage
{
    public function __construct(private readonly string $projectDir)
    {
        parent::__construct($this->projectDir.'/var/space-trader/', 'contracts.json');
    }

    protected function getIndexKey(): string
    {
        return 'contractId';
    }
}
