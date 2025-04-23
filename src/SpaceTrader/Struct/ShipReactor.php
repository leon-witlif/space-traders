<?php

declare(strict_types=1);

namespace App\SpaceTrader\Struct;

readonly class ShipReactor
{
    use FromRequestTrait;

    public function __construct(
        public string $symbol,
        public string $name,
        public string $description,
        public float $condition,
        public float $integrity,
        public int $powerOutput,
        public ShipRequirements $requirements,
        public int $quality,
    ) {
    }
}
