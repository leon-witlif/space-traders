<?php

declare(strict_types=1);

namespace App\Contract\Invoker;

use App\Contract\Contract;
use App\Contract\Task;
use App\SpaceTrader\ApiRegistry;

final class TaskParentInvoker
{
    /** @var \ReflectionClass<Task> */
    private readonly \ReflectionClass $parentReflection;
    private readonly \ReflectionMethod $parentConstructor;

    public function __construct(private readonly ApiRegistry $apiRegistry)
    {
        $this->parentReflection = new \ReflectionClass(Task::class);
        $this->parentConstructor = $this->parentReflection->getConstructor();
    }

    public function invoke(Task $task, Contract $contract): void
    {
        $this->parentConstructor->invoke($task, $contract, $this->apiRegistry);
    }
}
