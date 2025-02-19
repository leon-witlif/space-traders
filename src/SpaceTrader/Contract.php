<?php

declare(strict_types=1);

namespace App\SpaceTrader;

readonly class Contract
{
    public function __construct(
        public string $id,
        public string $factionSymbol,
        public string $type,
        public array $terms,
        public bool $accepted,
        public bool $fulfilled,
        public string $expiration,
        public string $deadlineToAccept,
    ) {
    }
}
