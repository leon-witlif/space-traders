<?php

declare(strict_types=1);

namespace App\SpaceTrader;

readonly class Ship
{
    public function __construct(
        public string $symbol,
        public array $nav,
        public array $crew,
        public array $fuel,
        public array $cooldown,
        public array $frame,
        public array $reactor,
        public array $engine,
        public array $modules,
        public array $mounts,
        public array $registration,
        public array $cargo,
    ) {
    }
}
