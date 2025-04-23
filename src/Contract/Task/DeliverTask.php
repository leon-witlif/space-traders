<?php

declare(strict_types=1);

namespace App\Contract\Task;

use App\Contract\Task;

final class DeliverTask extends Task
{
    public function __construct(
        private readonly string $contractId,
        private readonly string $shipSymbol,
        /** @var array<int, string> */
        private readonly array $deliverGoods,
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
            if (in_array($cargoItem->symbol, $this->deliverGoods)) {
                $this->contractApi->deliver(
                    $agentToken,
                    $this->contractId,
                    $this->shipSymbol,
                    $cargoItem->symbol,
                    $cargoItem->units
                );
            }
        }

        $this->finished = true;
    }

    /**
     * @return array{0: string, 1: string, 2: array<int, string>}
     */
    protected function getArgs(): array
    {
        return [$this->contractId, $this->shipSymbol, $this->deliverGoods];
    }
}
