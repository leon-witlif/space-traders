<?php

declare(strict_types=1);

namespace App\Contract\Task;

use App\Contract\Contract;
use App\Contract\Task;
use App\SpaceTrader\ShipApi;

final class OrbitTask extends Task
{
    public function __construct(Contract $contract, private readonly string $shipSymbol)
    {
        parent::__construct($contract);
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
        $this->getApi(ShipApi::class)->orbit($agentToken, $this->shipSymbol);

        $this->finished = true;
    }
}
