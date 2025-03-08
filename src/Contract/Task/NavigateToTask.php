<?php

declare(strict_types=1);

namespace App\Contract\Task;

use App\Contract\Contract;
use App\Contract\Task;
use App\SpaceTrader\ShipApi;

final class NavigateToTask extends Task
{
    public function __construct(
        Contract $contract,
        private readonly string $shipSymbol,
        private readonly string $destination,
    ) {
        parent::__construct($contract);
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

        $ship = $this->getShip($agentToken, $this->shipSymbol);

        if ($ship->nav->status === 'IN_ORBIT') {
            if ($ship->nav->waypointSymbol === $this->destination) {
                $this->insertAfter($this->contract->createTask(RefuelTask::class, $this->shipSymbol));

                $this->finished = true;
            } else {
                $this->getApi(ShipApi::class)->navigate($agentToken, $this->shipSymbol, $this->destination);
            }
        }
    }
}
