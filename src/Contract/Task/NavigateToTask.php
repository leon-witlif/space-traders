<?php

declare(strict_types=1);

namespace App\Contract\Task;

use App\Contract\Contract;
use App\Contract\Task;
use App\SpaceTrader\ApiRegistry;

final class NavigateToTask extends Task
{
    public function __construct(
        Contract $contract,
        ApiRegistry $apiRegistry,
        private readonly string $shipSymbol,
        private readonly string $destination,
    ) {
        parent::__construct($contract, $apiRegistry);
    }

    /**
     * @return array<int, string>
     */
    protected function getArgs(): array
    {
        return [$this->shipSymbol, $this->destination];
    }

    public function execute(string $agentToken, mixed &$output): void
    {
        if ($this->previous::class !== OrbitTask::class) {
            $this->insertBefore($this->contract->createTask(OrbitTask::class, $this->shipSymbol));

            return;
        }

        $ship = $this->getShipApi()->get($agentToken, $this->shipSymbol, true);

        if ($ship->nav->status === 'IN_ORBIT') {
            if ($ship->nav->waypointSymbol === $this->destination) {
                $this->insertAfter($this->contract->createTask(RefuelTask::class, $this->shipSymbol));

                $this->finished = true;
            } else {
                $this->getShipApi()->navigate($agentToken, $this->shipSymbol, $this->destination);
            }
        }
    }
}
