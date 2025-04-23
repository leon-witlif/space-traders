<?php

declare(strict_types=1);

namespace App\SpaceTrader\Struct;

readonly class ShipModule
{
    use FromRequestTrait;

    public function __construct(
        public string $symbol,
        public ?int $capacity,
        public ?int $range,
        public string $name,
        public string $description,
        public ShipRequirements $requirements,
    ) {
    }
}
