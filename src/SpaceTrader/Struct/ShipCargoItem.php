<?php

declare(strict_types=1);

namespace App\SpaceTrader\Struct;

readonly class ShipCargoItem
{
    use FromRequestTrait;

    public function __construct(
        public string $symbol,
        public string $name,
        public string $description,
        public int $units,
    ) {
    }
}
