<?php

declare(strict_types=1);

namespace App\SpaceTrader\Struct;

readonly class Ship
{
    public function __construct(
        public string $symbol,
        /** @var array{systemSymbol: string, waypointSymbol: string, route: array{origin: array, destination: array, arrival: string, departureTime: string}, status: string, flightMode: string} */
        public array $nav,
        public array $crew,
        public array $fuel,
        /** @var array{shipSymbol: string, totalSeconds: int, remainingSeconds: int, expiration: string} */
        public array $cooldown,
        public array $frame,
        public array $reactor,
        public array $engine,
        public array $modules,
        public array $mounts,
        public array $registration,
        /** @var array{capacity: int, units: int, inventory: array} */
        public array $cargo,
    ) {
    }
}
