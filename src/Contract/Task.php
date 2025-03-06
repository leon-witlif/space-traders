<?php

declare(strict_types=1);

namespace App\Contract;

abstract class Task implements \JsonSerializable
{
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

    abstract protected function getName(): string;

    abstract protected function getArgs(): array;

    abstract public function execute(string $agentToken): mixed;

    public function __toString(): string
    {
        return $this->getName().($this->finished ? ' (Done)' : '');
    }

    public function jsonSerialize(): array
    {
        return [
            'task' => $this->getName(),
            'args' => $this->getArgs(),
            'finished' => $this->finished,
        ];
    }
}
