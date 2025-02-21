<?php

declare(strict_types=1);

namespace App\Contract;

use App\SpaceTrader\Struct\Agent;
use App\SpaceTrader\Struct\Contract;
use App\SpaceTrader\Struct\Ship;

class Procurement
{
    public readonly ?Contract $contract;
    public readonly ?Agent $agent;
    public readonly ?Ship $ship;

    private ProcurementAction $action;

    public function __construct(?Contract $contract = null, ?Agent $agent = null, ?Ship $ship = null, ProcurementAction $action = ProcurementAction::FIND_ASTEROID)
    {
        $this->contract = $contract;
        $this->agent = $agent;
        $this->ship = $ship;

        $this->action = $action;
    }

    public function getAction(): string
    {
        return $this->action->name;
    }
}
