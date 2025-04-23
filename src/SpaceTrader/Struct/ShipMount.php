<?php

declare(strict_types=1);

namespace App\SpaceTrader\Struct;

readonly class ShipMount
{
    use FromRequestTrait;

    public function __construct(
        public string $symbol,
        public string $name,
        public ?string $description,
        public ?int $strength,
        /** @var array<int, string> */
        public ?array $deposits,
        public ShipRequirements $requirements,
    ) {
    }
}
