<?php

declare(strict_types=1);

namespace App\Storage;

use App\Contract\Task;

/**
 * @phpstan-extends Storage<array{agentToken: string, contractId: string, shipSymbol: string, tasks: array<Task>}>
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
