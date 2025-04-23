<?php

declare(strict_types=1);

namespace App\SpaceTrader\Struct;

readonly class Ship
{
    use FromRequestTrait;

    public function __construct(
        public string $symbol,
        public ShipRegistration $registration,
        public ShipNav $nav,
        public ShipCrew $crew,
        public ShipFrame $frame,
        public ShipReactor $reactor,
        public ShipEngine $engine,
        public Cooldown $cooldown,
        /** @var array<int, ShipModule> */
        public array $modules,
        /** @var array<int, ShipMount> */
        public array $mounts,
        public ShipCargo $cargo,
        public ShipFuel $fuel,
    ) {
    }
}
