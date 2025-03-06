<?php

declare(strict_types=1);

namespace App\Contract\Task;

use App\Contract\Contract;
use App\Contract\Task;

final class FindAsteroidTask extends Task
{
    public function __construct(Contract $contract, private readonly string $type)
    {
        parent::__construct($contract);
    }

    protected function getName(): string
    {
        return self::class;
    }

    protected function getArgs(): array
    {
        return [$this->type];
    }

    public function execute(string $agentToken): mixed
    {
        // $agent = $this->contract->getApi(AgentApi::class)->get($agentToken);
        // $system = $this->contract->getApi(SystemApi::class)->get(Navigation::getSystem($agent->headquarters));
        //
        // $asteroid = array_find($system->waypoints, fn (SystemWaypoint $waypoint) => $waypoint->type === $this->type);
        //
        // return $asteroid->symbol;

        $this->finished = true;

        $this->insertAfter($this->contract->createTask(NavigateToTask::class, 'waypointSymbol'));

        return null;
    }
}
