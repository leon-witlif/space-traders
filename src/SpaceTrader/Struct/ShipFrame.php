<?php

declare(strict_types=1);

namespace App\SpaceTrader\Struct;

readonly class ShipFrame
{
    use FromRequestTrait;

    public function __construct(
        public string $symbol,
        public string $name,
        public string $description,
        public float $condition,
        public float $integrity,
        public int $moduleSlots,
        public int $mountingPoints,
        public int $fuelCapacity,
        public ShipRequirements $requirements,
        public int $quality,
    ) {
    }
}
