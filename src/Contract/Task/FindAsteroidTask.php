<?php

declare(strict_types=1);

namespace App\Contract\Task;

use App\Contract\Contract;
use App\Contract\Task;
use App\Helper\Navigation;
use App\SpaceTrader\ApiRegistry;
use App\SpaceTrader\Struct\SystemWaypoint;

final class FindAsteroidTask extends Task
{
    public function __construct(
        Contract $contract,
        ApiRegistry $apiRegistry,
        private readonly string $shipSymbol,
        private readonly string $type,
    ) {
        parent::__construct($contract, $apiRegistry);
    }

    /**
     * @return array{0: string, 1: string}
     */
    protected function getArgs(): array
    {
        return [$this->shipSymbol, $this->type];
    }

    public function execute(string $agentToken, mixed &$output): void
    {
        $agent = $this->getAgentApi()->get($agentToken, true);
        $system = $this->getSystemApi()->get(Navigation::getSystem($agent->headquarters), true);

        $asteroid = array_find($system->waypoints, fn (SystemWaypoint $waypoint) => $waypoint->type === $this->type);

        if ($asteroid) {
            $output = $asteroid->symbol;

            $this->finished = true;
        }
    }
}
