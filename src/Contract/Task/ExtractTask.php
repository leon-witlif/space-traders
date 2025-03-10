<?php

declare(strict_types=1);

namespace App\Contract\Task;

use App\Contract\Contract;
use App\Contract\Task;
use App\SpaceTrader\ApiRegistry;
use App\SpaceTrader\Struct\ShipCargo;

final class ExtractTask extends Task
{
    private int $currentCargoUnits = 0;

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
        if ($this->previous::class !== OrbitTask::class) {
            $this->insertBefore($this->contract->createTask(OrbitTask::class, $this->shipSymbol));

            return;
        }

        $ship = $this->getShipApi()->get($agentToken, $this->shipSymbol, true);

        $this->currentCargoUnits = $ship->cargo->units;

        if ($ship->cooldown->remainingSeconds > 0) {
            return;
        }

        $output = $this->getShipApi()->extract($agentToken, $this->shipSymbol);

        if ($this->isShipCargoFull($output['cargo'])) {
            $this->finished = true;
        }

        $this->currentCargoUnits = $output['cargo']->units;
    }

    public function __toString(): string
    {
        if ($this->finished) {
            return parent::__toString();
        } else {
            return parent::__toString().' ('.$this->currentCargoUnits.')';
        }
    }

    private function isShipCargoFull(ShipCargo $shipCargo): bool
    {
        return $shipCargo->units >= $shipCargo->capacity;
    }
}
