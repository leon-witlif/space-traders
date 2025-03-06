<?php

declare(strict_types=1);

namespace App\Contract\Task;

use App\Contract\Task;

final class DockShipTask extends Task
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
        $this->finished = true;

        return null;
    }
}
