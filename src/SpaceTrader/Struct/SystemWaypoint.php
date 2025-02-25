<?php

declare(strict_types=1);

namespace App\SpaceTrader\Struct;

readonly class SystemWaypoint
{
    use FromRequestTrait;

    /** @phpstan-ignore-next-line missingType.iterableValue */
    public function __construct(
        public string $symbol,
        public string $type,
        public int $x,
        public int $y,
        public array $orbitals,
        public ?string $orbits,
    ) {
    }
}
