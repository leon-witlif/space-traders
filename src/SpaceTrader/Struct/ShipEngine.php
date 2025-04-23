<?php

declare(strict_types=1);

namespace App\SpaceTrader\Struct;

readonly class ShipEngine
{
    use FromRequestTrait;

    public function __construct(
        public string $symbol,
        public string $name,
        public string $description,
        public float $condition,
        public float $integrity,
        public int $speed,
        public ShipRequirements $requirements,
        public int $quality,
    ) {
    }
}
