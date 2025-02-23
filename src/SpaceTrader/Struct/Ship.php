<?php

declare(strict_types=1);

namespace App\SpaceTrader\Struct;

readonly class Ship
{
    use FromRequestTrait;

    public function __construct(
        public string $symbol,
        public array $registration,
        /** @var array{systemSymbol: string, waypointSymbol: string, route: array{destination: array, origin: array, departureTime: string, arrival: string}, status: string, flightMode: string} */
        public array $nav,
        public array $crew,
        public array $frame,
        public array $reactor,
        public array $engine,
        /** @var array{shipSymbol: string, totalSeconds: int, remainingSeconds: int, expiration: string} */
        public array $cooldown,
        public array $modules,
        public array $mounts,
        /** @var array{capacity: int, units: int, inventory: array} */
        public array $cargo,
        public array $fuel,
    ) {
    }
}
