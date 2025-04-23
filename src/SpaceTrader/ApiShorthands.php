<?php

declare(strict_types=1);

namespace App\SpaceTrader;

use App\SpaceTrader\Endpoint\AgentApi;
use App\SpaceTrader\Endpoint\ContractApi;
use App\SpaceTrader\Endpoint\FactionApi;
use App\SpaceTrader\Endpoint\FleetApi;
use App\SpaceTrader\Endpoint\GlobalApi;
use App\SpaceTrader\Endpoint\SystemApi;

trait ApiShorthands
{
    protected GlobalApi $globalApi {
        get => $this->apiRegistry->getApi(GlobalApi::class);
    }

    protected AgentApi $agentApi {
        get => $this->apiRegistry->getApi(AgentApi::class);
    }

    protected ContractApi $contractApi {
        get => $this->apiRegistry->getApi(ContractApi::class);
    }

    protected FactionApi $factionApi {
        get => $this->apiRegistry->getApi(FactionApi::class);
    }

    protected FleetApi $fleetApi {
        get => $this->apiRegistry->getApi(FleetApi::class);
    }

    protected SystemApi $systemApi {
        get => $this->apiRegistry->getApi(SystemApi::class);
    }
}
