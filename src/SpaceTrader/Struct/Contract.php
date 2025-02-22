<?php

declare(strict_types=1);

namespace App\SpaceTrader\Struct;

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
