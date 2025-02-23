<?php

declare(strict_types=1);

namespace App\SpaceTrader\Struct;

readonly class ShipNavRoute
{
    use FromRequestTrait;

    public function __construct(
        public ShipNavRouteWaypoint $destination,
        public ShipNavRouteWaypoint $origin,
        public string $departureTime,
        public string $arrival,
    ) {
    }
}
