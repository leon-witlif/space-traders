<?php

declare(strict_types=1);

namespace App\SpaceTrader\Struct;

readonly class Waypoint
{
    use FromRequestTrait;

    /** @phpstan-ignore-next-line missingType.iterableValue */
    public function __construct(
        public string $symbol,
        public string $type,
        public string $systemSymbol,
        public int $x,
        public int $y,
        public array $orbitals,
        public ?string $orbits,
        public ?WaypointFaction $faction,
        public array $traits,
        public ?array $modifiers,
        public ?array $chart,
        public bool $isUnderConstruction,
    ) {
    }
}
