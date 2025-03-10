<?php

declare(strict_types=1);

namespace App\Contract\Task;

use App\Contract\Contract;
use App\Contract\Task;
use App\SpaceTrader\ApiRegistry;

final class DockTask extends Task
{
    public function __construct(
        Contract $contract,
        ApiRegistry $apiRegistry,
        private readonly string $shipSymbol
    ) {
        parent::__construct($contract, $apiRegistry);
    }

    /**
     * @return array<int, string>
     */
    protected function getArgs(): array
    {
        return [$this->shipSymbol];
    }

    public function execute(string $agentToken, mixed &$output): void
    {
        $this->getShipApi()->dock($agentToken, $this->shipSymbol);

        $this->finished = true;
    }
}
