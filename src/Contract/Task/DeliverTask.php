<?php

declare(strict_types=1);

namespace App\Contract\Task;

use App\Contract\Contract;
use App\Contract\Task;
use App\SpaceTrader\ApiRegistry;

final class DeliverTask extends Task
{
    public function __construct(
        Contract $contract,
        ApiRegistry $apiRegistry,
        private readonly string $contractId,
        private readonly string $shipSymbol,
        /** @var array<int, string> */
        private readonly array $deliverGoods,
    ) {
        parent::__construct($contract, $apiRegistry);
    }

    /**
     * @return array{0: string, 1: string, 2: array<int, string>}
     */
    protected function getArgs(): array
    {
        return [$this->contractId, $this->shipSymbol, $this->deliverGoods];
    }

    public function execute(string $agentToken, mixed &$output): void
    {
        if ($this->previous::class !== DockTask::class) {
            $this->insertBefore($this->contract->createTask(DockTask::class, $this->shipSymbol));

            return;
        }

        $ship = $this->getShipApi()->get($agentToken, $this->shipSymbol, true);

        foreach ($ship->cargo->inventory as $cargoItem) {
            if (in_array($cargoItem->symbol, $this->deliverGoods)) {
                $this->getContractApi()->deliver(
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
}
