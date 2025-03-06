<?php

declare(strict_types=1);

namespace App\Contract;

abstract class Contract
{
    protected Task $task;

    public function __construct(private readonly array $apis)
    {
    }

    /**
     * @phpstan-template T of object
     * @phpstan-param class-string<T> $className
     * @phpstan-return T
     */
    public function getApi(string $className): object
    {
        return $this->apis[$className];
    }

    /**
     * @phpstan-template T of Task
     * @phpstan-param class-string<T> $className
     * @phpstan-return T
     */
    public function createTask(string $className, mixed ...$args): Task
    {
        return new $className($this, ...$args);
    }

    public function restoreState(string $json): void
    {
        $tasks = json_decode($json);

        $taskReflection = new \ReflectionClass(Task::class);
        $finishedProperty = $taskReflection->getProperty('finished');

        // @formatter:off
        $tasks = array_map(function (object $task) use ($finishedProperty) {
            $instance = $this->createTask($task->task, ...$task->args);
            $finishedProperty->setValue($instance, $task->finished);
            return $instance;
        }, $tasks);
        // @formatter:on

        $this->task = $tasks[0];

        if (count($tasks) > 1) {
            $this->task->next = $tasks[1];

            for ($i = 1; $i < count($tasks) - 1; ++$i) {
                $tasks[$i]->previous = $tasks[$i - 1];
                $tasks[$i]->next = $tasks[$i + 1];
            }

            $tasks[count($tasks) - 1]->previous = $tasks[count($tasks) - 2];
        }
    }

    public function saveState(): string
    {
        $tasks = [];

        foreach ($this->taskIterator() as $task) {
            $tasks[] = $task;
        }

        return json_encode($tasks, JSON_PRETTY_PRINT);
    }

    public function execute(string $agentToken): void
    {
        foreach ($this->taskIterator() as $task) {
            if (!$task->finished) {
                $task->execute($agentToken);

                if (!$task->finished) {
                    break;
                }
            }
        }
    }

    public function __toString(): string
    {
        $output = '';

        foreach ($this->taskIterator() as $task) {
            $output .= $task.PHP_EOL;
        }

        return $output;
    }

    private function taskIterator(): \Generator
    {
        $currentTask = $this->task;

        while ($currentTask) {
            yield $currentTask;
            $currentTask = $currentTask->next;
        }
    }
}
