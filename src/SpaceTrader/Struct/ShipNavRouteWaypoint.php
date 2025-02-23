<?php

declare(strict_types=1);

namespace App\SpaceTrader\Struct;

readonly class ShipNavRouteWaypoint
{
    use FromRequestTrait;

    public function __construct(
        public string $symbol,
        public string $type,
        public string $systemSymbol,
        public int $x,
        public int $y,
    ) {
    }
}
