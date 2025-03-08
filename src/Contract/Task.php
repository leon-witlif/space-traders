<?php

declare(strict_types=1);

namespace App\Contract;

abstract class Task implements \JsonSerializable
{
    use ApiAware;
    use ListBehaviour;

    public ?Task $previous;
    public ?Task $next;

    protected readonly Contract $contract;
    protected(set) bool $finished;

    public function __construct(Contract $contract)
    {
        $this->previous = null;
        $this->next = null;

        $this->contract = $contract;
        $this->finished = false;
    }

    public function overwriteState(bool $finished): void
    {
        $this->finished = $finished;
    }

    /**
     * @return array<int, mixed>
     */
    protected function getArgs(): array
    {
        return [];
    }

    abstract public function execute(string $agentToken, mixed &$output): void;

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
