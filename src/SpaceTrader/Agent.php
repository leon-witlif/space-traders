<?php

declare(strict_types=1);

namespace App\SpaceTrader;

readonly class Agent
{
    public function __construct(
        public string $accountId,
        public string $symbol,
        public string $headquarters,
        public int $credits,
        public string $startingFaction,
        public int $shipCount,
    ) {
    }
}
