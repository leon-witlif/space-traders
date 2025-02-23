<?php

declare(strict_types=1);

namespace App\SpaceTrader\Struct;

readonly class Ship
{
    use FromRequestTrait;

    public function __construct(
        public string $symbol,
        public array $registration,
        public ShipNav $nav,
        public array $crew,
        public array $frame,
        public array $reactor,
        public array $engine,
        public Cooldown $cooldown,
        public array $modules,
        public array $mounts,
        public ShipCargo $cargo,
        public array $fuel,
    ) {
    }
}
