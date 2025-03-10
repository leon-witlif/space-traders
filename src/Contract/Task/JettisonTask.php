<?php

declare(strict_types=1);

namespace App\Contract\Task;

use App\Contract\Contract;
use App\Contract\Task;
use App\SpaceTrader\ApiRegistry;

final class JettisonTask extends Task
{
    public function __construct(
        Contract $contract,
        ApiRegistry $apiRegistry,
        private readonly string $shipSymbol,
        /** @var array<int, string> */
        private readonly array $whitelist,
    ) {
        parent::__construct($contract, $apiRegistry);
    }

    /**
     * @return array{0: string, 1: array<int, string>}
     */
    protected function getArgs(): array
    {
        return [$this->shipSymbol, $this->whitelist];
    }

    public function execute(string $agentToken, mixed &$output): void
    {
        $ship = $this->getShipApi()->get($agentToken, $this->shipSymbol, true);

        foreach ($ship->cargo->inventory as $cargoItem) {
            if (!in_array($cargoItem->symbol, $this->whitelist)) {
                $this->getShipApi()->jettison(
                    $agentToken,
                    $this->shipSymbol,
                    $cargoItem->symbol,
                    $cargoItem->units
                );
            }
        }

        $this->finished = true;
    }
}
