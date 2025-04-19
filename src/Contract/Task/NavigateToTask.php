<?php

declare(strict_types=1);

namespace App\Contract\Task;

use App\Contract\Task;

final class NavigateToTask extends Task
{
    public function __construct(
        private readonly string $shipSymbol,
        private readonly string $destination,
    ) {
    }

    public function execute(string $agentToken, mixed &$output): void
    {
        if ($this->previous::class !== OrbitTask::class) {
            $this->insertBefore($this->contract->initializeTask(new OrbitTask($this->shipSymbol)));

            return;
        }

        $ship = $this->getShipApi()->get($agentToken, $this->shipSymbol, true);

        if ($ship->nav->status === 'IN_ORBIT') {
            if ($ship->nav->waypointSymbol === $this->destination) {
                $this->insertAfter($this->contract->initializeTask(new RefuelTask($this->shipSymbol)));

                $this->finished = true;
            } else {
                $this->getShipApi()->navigate($agentToken, $this->shipSymbol, $this->destination);
            }
        }
    }

    /**
     * @return array{0: string, 1: string}
     */
    protected function getArgs(): array
    {
        return [$this->shipSymbol, $this->destination];
    }
}
