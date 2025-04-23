<?php

declare(strict_types=1);

namespace App\SpaceTrader\Struct;

readonly class ShipCrew
{
    use FromRequestTrait;

    public function __construct(
        public int $current,
        public int $required,
        public int $capacity,
        public string $rotation,
        public int $morale,
        public int $wages,
    ) {
    }
}
