<?php

declare(strict_types=1);

namespace App\SpaceTrader;

use App\SpaceTrader\Endpoint\AgentApi;
use App\SpaceTrader\Endpoint\ContractApi;
use App\SpaceTrader\Endpoint\FactionApi;
use App\SpaceTrader\Endpoint\FleetApi;
use App\SpaceTrader\Endpoint\GlobalApi;
use App\SpaceTrader\Endpoint\SystemApi;

class ApiRegistry
{
    /** @var array<class-string<GlobalApi|AgentApi|ContractApi|FactionApi|FleetApi|SystemApi>, ApiEndpoint> */
    private array $apis;

    public function __construct(
        GlobalApi $globalApi,
        AgentApi $agentApi,
        ContractApi $contractApi,
        FactionApi $factionApi,
        FleetApi $fleetApi,
        SystemApi $systemApi,
    ) {
        $this->apis = [
            GlobalApi::class => $globalApi,
            AgentApi::class => $agentApi,
            ContractApi::class => $contractApi,
            FactionApi::class => $factionApi,
            FleetApi::class => $fleetApi,
            SystemApi::class => $systemApi,
        ];
    }

    /**
     * @phpstan-template T
     * @phpstan-param class-string<T> $className
     * @phpstan-return T
     */
    public function getApi(string $className)
    {
        return $this->apis[$className];
    }
}
