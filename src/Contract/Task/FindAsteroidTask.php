<?php

declare(strict_types=1);

namespace App\Contract\Task;

use App\Contract\Task;
use App\Helper\Navigation;
use App\SpaceTrader\Struct\SystemWaypoint;

final class FindAsteroidTask extends Task
{
    public function __construct(private readonly string $type)
    {
    }

    public function execute(string $agentToken, mixed &$output): void
    {
        $agent = $this->agentApi->get($agentToken, true);
        $system = $this->systemApi->get(Navigation::getSystem($agent->headquarters), true);

        $asteroid = array_find($system->waypoints, fn (SystemWaypoint $waypoint) => $waypoint->type === $this->type);

        if ($asteroid) {
            $output = $asteroid->symbol;

            $this->finished = true;
        }
    }

    /**
     * @return array{0: string}
     */
    protected function getArgs(): array
    {
        return [$this->type];
    }
}
