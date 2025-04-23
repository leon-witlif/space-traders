<?php

declare(strict_types=1);

namespace App\Contract;

use App\SpaceTrader\ApiRegistry;
use App\SpaceTrader\ApiShorthands;

abstract class Task implements \JsonSerializable
{
    use ApiShorthands;
    use ListBehaviour;

    public ?Task $previous;
    public ?Task $next;

    public bool $finished;

    private function __construct(
        protected readonly Contract $contract,
        protected readonly ApiRegistry $apiRegistry,
    ) {
        $this->previous = null;
        $this->next = null;

        $this->finished = false;
    }

    abstract public function execute(string $agentToken, mixed &$output): void;

    /**
     * @return array<int, mixed>
     */
    protected function getArgs(): array
    {
        return [];
    }

    public function __toString(): string
    {
        return static::class.($this->finished ? ' (Done)' : '');
    }

    /**
     * @return array{task: class-string, args: array<int, mixed>, finished: bool}
     */
    public function jsonSerialize(): array
    {
        return [
            'task' => static::class,
            'args' => $this->getArgs(),
            'finished' => $this->finished,
        ];
    }
}
