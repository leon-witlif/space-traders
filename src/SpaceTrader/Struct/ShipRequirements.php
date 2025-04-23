<?php

declare(strict_types=1);

namespace App\SpaceTrader\Struct;

readonly class ShipRequirements
{
    use FromRequestTrait;

    public function __construct(
        public ?int $power,
        public ?int $crew,
        public ?int $slots,
    ) {
    }
}
