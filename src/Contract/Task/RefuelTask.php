<?php

declare(strict_types=1);

namespace App\Contract\Task;

use App\Contract\Contract;
use App\Contract\Task;
use App\SpaceTrader\ShipApi;

final class RefuelTask extends Task
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
        if ($this->previous::class !== DockTask::class) {
            $this->insertBefore($this->contract->createTask(DockTask::class, $this->shipSymbol));

            return;
        }

        $this->getApi(ShipApi::class)->refuel($agentToken, $this->shipSymbol);

        $this->insertAfter($this->contract->createTask(ExtractTask::class, $this->shipSymbol));

        $this->finished = true;
    }
}
