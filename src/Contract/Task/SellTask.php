<?php

declare(strict_types=1);

namespace App\Contract\Task;

use App\Contract\Task;

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
            $this->insertBefore($this->contract->initializeTask(new DockTask($this->shipSymbol)));

            return;
        }

        $ship = $this->getShipApi()->get($agentToken, $this->shipSymbol, true);

        foreach ($ship->cargo->inventory as $cargoItem) {
            if (in_array($cargoItem->symbol, $this->sellCargoItems)) {
                $this->getShipApi()->sell(
                    $agentToken,
                    $this->shipSymbol,
                    $cargoItem->symbol,
                    $cargoItem->units
                );
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
