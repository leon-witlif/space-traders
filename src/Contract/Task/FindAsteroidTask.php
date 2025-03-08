<?php

declare(strict_types=1);

namespace App\Contract\Task;

use App\Contract\Contract;
use App\Contract\Task;
use App\SpaceTrader\Struct\SystemWaypoint;

final class FindAsteroidTask extends Task
{
    public function __construct(Contract $contract, private readonly string $type)
    {
        parent::__construct($contract);
    }

    /**
     * @return array<int, string>
     */
    protected function getArgs(): array
    {
        return [$this->type];
    }

    public function execute(string $agentToken, mixed &$output): void
    {
        $agent = $this->getAgent($agentToken);
        $system = $this->getSystem($agent->headquarters);

        $asteroid = array_find($system->waypoints, fn (SystemWaypoint $waypoint) => $waypoint->type === $this->type);

        if ($asteroid) {
            $this->insertAfter($this->contract->createTask(NavigateToTask::class, $this->contract->shipSymbol, $asteroid->symbol));

            $this->finished = true;
        }
    }
}
