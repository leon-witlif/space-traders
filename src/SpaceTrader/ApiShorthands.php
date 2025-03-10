<?php

declare(strict_types=1);

namespace App\SpaceTrader;

use App\SpaceTrader\Endpoint\AgentApi;
use App\SpaceTrader\Endpoint\ContractApi;
use App\SpaceTrader\Endpoint\ShipApi;
use App\SpaceTrader\Endpoint\SystemApi;

trait ApiShorthands
{
    protected function getAgentApi(): AgentApi
    {
        return $this->apiRegistry->getApi(AgentApi::class);
    }

    protected function getContractApi(): ContractApi
    {
        return $this->apiRegistry->getApi(ContractApi::class);
    }

    protected function getShipApi(): ShipApi
    {
        return $this->apiRegistry->getApi(ShipApi::class);
    }

    protected function getSystemApi(): SystemApi
    {
        return $this->apiRegistry->getApi(SystemApi::class);
    }
}
