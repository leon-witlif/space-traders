<?php

declare(strict_types=1);

namespace App\SpaceTrader\Struct;

readonly class Market
{
    use FromRequestTrait;

    /** @phpstan-ignore-next-line missingType.iterableValue */
    public function __construct(
        public string $symbol,
        public array $exports,
        public array $imports,
        public array $exchange,
        public ?array $transactions,
        public ?array $tradeGoods,
    ) {
    }
}
