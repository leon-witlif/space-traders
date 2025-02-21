<?php

declare(strict_types=1);

namespace App\SpaceTrader\Struct;

use App\Contract\Procurement;

class Contract
{
    public Procurement $activeContract;

    public function __construct(
        public readonly string $id,
        public readonly string $factionSymbol,
        public readonly string $type,
        public readonly array $terms,
        public readonly bool $accepted,
        public readonly bool $fulfilled,
        public readonly string $expiration,
        public readonly string $deadlineToAccept,
    ) {
    }

    public function getTermsDescription(): string
    {
        return sprintf(
            'Deliver %d / %d %s to %s',
            $this->terms['deliver'][0]['unitsFulfilled'],
            $this->terms['deliver'][0]['unitsRequired'],
            $this->terms['deliver'][0]['tradeSymbol'],
            $this->terms['deliver'][0]['destinationSymbol'],
        );
    }

    public function getRewardDescription(): string
    {
        return sprintf(
            '%d credits immediately, %d credits on fulfillment',
            $this->terms['payment']['onAccepted'],
            $this->terms['payment']['onFulfilled'],
        );
    }
}
