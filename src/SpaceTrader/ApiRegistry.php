<?php

declare(strict_types=1);

namespace App\SpaceTrader;

use App\SpaceTrader\Endpoint\AgentApi;
use App\SpaceTrader\Endpoint\ContractApi;
use App\SpaceTrader\Endpoint\ShipApi;
use App\SpaceTrader\Endpoint\SystemApi;

class ApiRegistry
{
    /** @var array<class-string<AgentApi|ContractApi|ShipApi|SystemApi>, ApiEndpoint> */
    private array $apis;

    public function __construct(
        AgentApi $agentApi,
        ContractApi $contractApi,
        ShipApi $shipApi,
        SystemApi $systemApi,
    ) {
        $this->apis = [
            $agentApi::class => $agentApi,
            $contractApi::class => $contractApi,
            $shipApi::class => $shipApi,
            $systemApi::class => $systemApi,
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
