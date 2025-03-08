<?php

declare(strict_types=1);

namespace App\Contract\Task;

use App\Contract\Contract;
use App\Contract\Task;
use App\SpaceTrader\ShipApi;

final class ExtractTask extends Task
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
        if ($this->previous::class !== OrbitTask::class) {
            $this->insertBefore($this->contract->createTask(OrbitTask::class, $this->shipSymbol));

            return;
        }

        $ship = $this->getShip($agentToken, $this->shipSymbol);

        if ($ship->cooldown->remainingSeconds > 0) {
            return;
        }

        $output = $this->getApi(ShipApi::class)->extract($agentToken, $this->shipSymbol);

        if ($output['cargo']->units >= $output['cargo']->capacity) {
            $this->finished = true;
        }
    }
}
