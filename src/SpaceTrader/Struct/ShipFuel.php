<?php

declare(strict_types=1);

namespace App\SpaceTrader\Struct;

readonly class ShipFuel
{
    use FromRequestTrait;

    /** @phpstan-ignore-next-line missingType.iterableValue */
    public function __construct(
        public int $current,
        public int $capacity,
        public array $consumed,
    ) {
    }
}
