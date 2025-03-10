<?php

declare(strict_types=1);

namespace App\SpaceTrader\Struct;

readonly class ContractDeliverGood
{
    use FromRequestTrait;

    public function __construct(
        public string $tradeSymbol,
        public string $destinationSymbol,
        public int $unitsRequired,
        public int $unitsFulfilled,
    ) {
    }
}
