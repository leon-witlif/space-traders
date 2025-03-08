<?php

declare(strict_types=1);

namespace App\Contract\Task;

use App\Contract\Contract;
use App\Contract\Task;
use App\SpaceTrader\ShipApi;

final class JettisonTask extends Task
{
    public function __construct(
        Contract $contract,
        private readonly string $shipSymbol,
        /** @var array<int, string> */
        private readonly array $whitelist,
    ) {
        parent::__construct($contract);
    }

    /**
     * @return array{0: string, 1: array<string>}
     */
    protected function getArgs(): array
    {
        return [$this->shipSymbol, $this->whitelist];
    }

    public function execute(string $agentToken, mixed &$output): void
    {
        $ship = $this->getShip($agentToken, $this->shipSymbol);

        foreach ($ship->cargo->inventory as $item) {
            if (!in_array($item->symbol, $this->whitelist)) {
                $this->getApi(ShipApi::class)->jettison(
                    $agentToken,
                    $this->shipSymbol,
                    $item->symbol,
                    $item->units
                );
            }
        }

        $this->finished = true;
    }
}
