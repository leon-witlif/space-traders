<?php

declare(strict_types=1);

namespace App\Contract\Task;

use App\Contract\Contract;
use App\Contract\Task;

final class NavigateToTask extends Task
{
    public function __construct(Contract $contract, private readonly string $destination)
    {
        parent::__construct($contract);
    }

    protected function getName(): string
    {
        return self::class;
    }

    protected function getArgs(): array
    {
        return [$this->destination];
    }

    public function execute(string $agentToken): mixed
    {
        if ($this->previous::class !== OrbitShipTask::class) {
            $this->insertBefore($this->contract->createTask(OrbitShipTask::class));

            return null;
        }

        $this->finished = true;

        $this->insertAfter($this->contract->createTask(RefuelShipTask::class));

        return null;
    }
}
