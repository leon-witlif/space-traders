<?php

declare(strict_types=1);

namespace App\Contract\Task;

use App\Contract\Task;
use App\SpaceTrader\Struct\ShipCargo;

final class ExtractTask extends Task
{
    private int $currentCargoUnits = 0;

    public function __construct(private readonly string $shipSymbol)
    {
    }

    public function execute(string $agentToken, mixed &$output): void
    {
        if ($this->previous::class !== OrbitTask::class) {
            $this->insertBefore($this->contract->invokeTaskParent(new OrbitTask($this->shipSymbol)));

            return;
        }

        $ship = $this->fleetApi->get($agentToken, $this->shipSymbol, true);

        $this->currentCargoUnits = $ship->cargo->units;

        if ($ship->cooldown->remainingSeconds > 0) {
            return;
        }

        $output = $this->fleetApi->extract($agentToken, $this->shipSymbol);

        if ($this->isShipCargoFull($output['cargo'])) {
            $this->finished = true;
        }

        $this->currentCargoUnits = $output['cargo']->units;
    }

    private function isShipCargoFull(ShipCargo $shipCargo): bool
    {
        return $shipCargo->units >= $shipCargo->capacity;
    }

    /**
     * @return array{0: string}
     */
    protected function getArgs(): array
    {
        return [$this->shipSymbol];
    }

    public function __toString(): string
    {
        if ($this->finished) {
            return parent::__toString();
        } else {
            return parent::__toString().' ('.$this->currentCargoUnits.')';
        }
    }
}
