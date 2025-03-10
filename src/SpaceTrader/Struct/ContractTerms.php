<?php

declare(strict_types=1);

namespace App\SpaceTrader\Struct;

readonly class ContractTerms
{
    use FromRequestTrait;

    public function __construct(
        public string $deadline,
        public ContractPayment $payment,
        /** @var array<int, ContractDeliverGood> */
        public ?array $deliver,
    ) {
    }
}
