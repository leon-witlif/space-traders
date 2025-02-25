<?php

declare(strict_types=1);

namespace App\Storage;

/**
 * @phpstan-extends Storage<array{token: string, symbol: string}>
 */
class AgentStorage extends Storage
{
    public function __construct(private readonly string $projectDir)
    {
        parent::__construct($this->projectDir.'/var/space-trader/', 'agents.json');
    }

    protected function getIndexKey(): string
    {
        return 'symbol';
    }
}
