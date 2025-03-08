<?php

declare(strict_types=1);

namespace App\Contract\Task;

use App\Contract\Task;

final class DeliverTask extends Task
{
    public function execute(string $agentToken, mixed &$output): void
    {
    }
}
