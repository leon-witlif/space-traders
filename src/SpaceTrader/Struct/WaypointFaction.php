<?php

declare(strict_types=1);

namespace App\SpaceTrader\Struct;

readonly class WaypointFaction
{
    use FromRequestTrait;

    public function __construct(
        public string $symbol,
    ) {
    }
}
