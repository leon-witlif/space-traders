<?php

declare(strict_types=1);

namespace App\SpaceTrader\Struct;

readonly class ContractPayment
{
    use FromRequestTrait;

    public function __construct(
        public int $onAccepted,
        public int $onFulfilled,
    ) {
    }
}
