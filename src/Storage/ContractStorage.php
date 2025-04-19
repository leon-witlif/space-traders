<?php

declare(strict_types=1);

namespace App\Storage;

use App\Contract\Contract;

/**
 * @phpstan-extends Storage<array{
 *     agentToken: string,
 *     contractId: string,
 *     shipSymbol: string,
 *     tasks: Contract|array<int, array{task: class-string, args: array<int, mixed>, finished: bool}>,
 *     marketplaceSymbol?: string,
 *     asteroidSymbol?: string,
 * }>
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
