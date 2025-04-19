<?php

declare(strict_types=1);

namespace App\SpaceTrader\Struct;

readonly class Shipyard
{
    use FromRequestTrait;

    /** @phpstan-ignore-next-line missingType.iterableValue */
    public function __construct(
        public string $symbol,
        public array $shipTypes,
        public ?array $transactions,
        public ?array $ships,
        public int $modificationsFee
    ) {
    }
}
