<?php

declare(strict_types=1);

namespace App\Contract\Task;

use App\Contract\Task;

final class RefuelShipTask extends Task
{
    protected function getName(): string
    {
        return self::class;
    }

    protected function getArgs(): array
    {
        return [];
    }

    public function execute(string $agentToken): mixed
    {
        if ($this->previous::class !== DockShipTask::class) {
            $this->insertBefore($this->contract->createTask(DockShipTask::class));

            return null;
        }

        $this->finished = true;

        return null;
    }
}
