<?php

declare(strict_types=1);

namespace App\SpaceTrader;

use App\SpaceTrader\Endpoint\AgentApi;
use App\SpaceTrader\Endpoint\ContractApi;
use App\SpaceTrader\Endpoint\FactionApi;
use App\SpaceTrader\Endpoint\GlobalApi;
use App\SpaceTrader\Endpoint\ShipApi;
use App\SpaceTrader\Endpoint\SystemApi;

class ApiRegistry
{
    /** @var array<class-string<GlobalApi|AgentApi|ContractApi|ShipApi|SystemApi|FactionApi>, ApiEndpoint> */
    private array $apis;

    public function __construct(
        GlobalApi $globalApi,
        AgentApi $agentApi,
        ContractApi $contractApi,
        ShipApi $shipApi,
        SystemApi $systemApi,
        FactionApi $factionApi,
    ) {
        $this->apis = [
            $globalApi::class => $globalApi,
            $agentApi::class => $agentApi,
            $contractApi::class => $contractApi,
            $shipApi::class => $shipApi,
            $systemApi::class => $systemApi,
            $factionApi::class => $factionApi,
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
