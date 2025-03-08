<?php

declare(strict_types=1);

namespace App\Contract;

use App\Helper\Navigation;
use App\SpaceTrader\AgentApi;
use App\SpaceTrader\ContractApi;
use App\SpaceTrader\ShipApi;
use App\SpaceTrader\Struct\Agent;
use App\SpaceTrader\Struct\Contract as ContractStruct;
use App\SpaceTrader\Struct\Ship;
use App\SpaceTrader\Struct\System;
use App\SpaceTrader\SystemApi;

trait ApiAware
{
    /**
     * @phpstan-template T of object
     * @phpstan-param class-string<T> $className
     * @phpstan-return T
     */
    protected function getApi(string $className): object
    {
        return $this->contract->getApi($className);
    }

    protected function getAgent(string $agentToken): Agent
    {
        return $this->getApi(AgentApi::class)->get($agentToken, true);
    }

    protected function getContract(string $agentToken, string $contractId): ContractStruct
    {
        return $this->getApi(ContractApi::class)->get($agentToken, $contractId, true);
    }

    protected function getShip(string $agentToken, string $shipSymbol): Ship
    {
        return $this->getApi(ShipApi::class)->get($agentToken, $shipSymbol, true);
    }

    protected function getSystem(string $systemSymbol): System
    {
        return $this->getApi(SystemApi::class)->get(Navigation::getSystem($systemSymbol), true);
    }
}
