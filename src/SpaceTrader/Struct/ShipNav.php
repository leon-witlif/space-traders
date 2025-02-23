<?php

declare(strict_types=1);

namespace App\SpaceTrader\Struct;

readonly class ShipNav
{
    use FromRequestTrait;

    public function __construct(
        public string $systemSymbol,
        public string $waypointSymbol,
        public ShipNavRoute $route,
        public string $status,
        public string $flightMode,
    ) {
    }
}
