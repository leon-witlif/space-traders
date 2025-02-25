<?php

declare(strict_types=1);

namespace App\SpaceTrader\Struct;

readonly class Faction
{
    use FromRequestTrait;

    /** @phpstan-ignore-next-line missingType.iterableValue */
    public function __construct(
        public string $symbol,
        public string $name,
        public string $description,
        public string $headquarters,
        public array $traits,
        public bool $isRecruiting,
    ) {
    }
}
