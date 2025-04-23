<?php

declare(strict_types=1);

namespace App\Contract\Task;

use App\Contract\Task;

final class JettisonTask extends Task
{
    public function __construct(
        private readonly string $shipSymbol,
        /** @var array<int, string> */
        private readonly array $whitelist = [],
    ) {
    }

    public function execute(string $agentToken, mixed &$output): void
    {
        $ship = $this->fleetApi->get($agentToken, $this->shipSymbol, true);

        foreach ($ship->cargo->inventory as $cargoItem) {
            if (!in_array($cargoItem->symbol, $this->whitelist)) {
                $this->fleetApi->jettison(
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
        return [$this->shipSymbol, $this->whitelist];
    }
}
