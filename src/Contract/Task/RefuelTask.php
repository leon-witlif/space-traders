<?php

declare(strict_types=1);

namespace App\Contract\Task;

use App\Contract\Task;
use App\SpaceTrader\Exception\ShipRefuelException;

final class RefuelTask extends Task
{
    public function __construct(private readonly string $shipSymbol)
    {
    }

    public function execute(string $agentToken, mixed &$output): void
    {
        if ($this->previous::class !== DockTask::class) {
            $this->insertBefore($this->contract->initializeTask(new DockTask($this->shipSymbol)));

            return;
        }

        try {
            $this->getShipApi()->refuel($agentToken, $this->shipSymbol);
        } catch (ShipRefuelException) {
        }

        $this->finished = true;
    }

    /**
     * @return array{0: string}
     */
    protected function getArgs(): array
    {
        return [$this->shipSymbol];
    }
}
