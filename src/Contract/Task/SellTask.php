<?php

declare(strict_types=1);

namespace App\Contract\Task;

use App\Contract\Task;
use App\SpaceTrader\Exception\ShipSellException;

final class SellTask extends Task
{
    public function __construct(
        private readonly string $shipSymbol,
        /** @var array<int, string> */
        private readonly array $sellCargoItems,
    ) {
    }

    public function execute(string $agentToken, mixed &$output): void
    {
        if ($this->previous::class !== DockTask::class) {
            $this->insertBefore($this->contract->invokeTaskParent(new DockTask($this->shipSymbol)));

            return;
        }

        $ship = $this->fleetApi->get($agentToken, $this->shipSymbol, true);

        foreach ($ship->cargo->inventory as $cargoItem) {
            if (in_array($cargoItem->symbol, $this->sellCargoItems)) {
                try {
                    $this->fleetApi->sell(
                        $agentToken,
                        $this->shipSymbol,
                        $cargoItem->symbol,
                        $cargoItem->units
                    );
                } catch (ShipSellException) {
                }
            }
        }

        $this->finished = true;
    }

    /**
     * @return array{0: string, 1: array<int, string>}
     */
    protected function getArgs(): array
    {
        return [$this->shipSymbol, $this->sellCargoItems];
    }
}
