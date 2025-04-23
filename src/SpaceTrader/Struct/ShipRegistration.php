<?php

declare(strict_types=1);

namespace App\SpaceTrader\Struct;

readonly class ShipRegistration
{
    use FromRequestTrait;

    public function __construct(
        public string $name,
        public string $factionSymbol,
        public string $role,
    ) {
    }
}
