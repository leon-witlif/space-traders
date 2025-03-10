<?php

declare(strict_types=1);

namespace App\SpaceTrader\Struct;

readonly class System
{
    use FromRequestTrait;

    public function __construct(
        public string $symbol,
        public string $sectorSymbol,
        public string $type,
        public int $x,
        public int $y,
        /** @var array<int, SystemWaypoint> */
        public array $waypoints,
        /** @var array<int, string> Array of Symbols */
        public array $factions,
    ) {
    }
}
