<?php

declare(strict_types=1);

namespace App\SpaceTrader\Struct;

readonly class ShipCargo
{
    use FromRequestTrait;

    public function __construct(
        public int $capacity,
        public int $units,
        /** @var array<int, ShipCargoItem> */
        public array $inventory,
    ) {
    }
}
