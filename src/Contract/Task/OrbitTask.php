<?php

declare(strict_types=1);

namespace App\Contract\Task;

use App\Contract\Task;

final class OrbitTask extends Task
{
    public function __construct(private readonly string $shipSymbol)
    {
    }

    public function execute(string $agentToken, mixed &$output): void
    {
        $this->fleetApi->orbit($agentToken, $this->shipSymbol);

        $this->finished = true;
    }

    /**
     * @return array{0: string}
     */
    protected function getArgs(): array
    {
        return [$this->shipSymbol];
    }
}
