<?php

declare(strict_types=1);

namespace App\SpaceTrader\Struct;

readonly class Cooldown
{
    use FromRequestTrait;

    public function __construct(
        public string $shipSymbol,
        public int $totalSeconds,
        public int $remainingSeconds,
        public ?string $expiration,
    ) {
    }
}
