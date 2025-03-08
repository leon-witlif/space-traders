<?php

declare(strict_types=1);

namespace App\Contract;

use App\SpaceTrader\AgentApi;
use App\SpaceTrader\ContractApi;
use App\SpaceTrader\ShipApi;
use App\SpaceTrader\SystemApi;

abstract class Contract implements \JsonSerializable
{
    protected Task $task;

    /**
     * @var array<class-string, object>
     */
    private array $apis;

    public function __construct(
        AgentApi $agentApi,
        ContractApi $contractApi,
        ShipApi $shipApi,
        SystemApi $systemApi,
    ) {
        $this->apis = [
            AgentApi::class => $agentApi,
            ContractApi::class => $contractApi,
            ShipApi::class => $shipApi,
            SystemApi::class => $systemApi,
        ];
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

    /**
     * @phpstan-template T of Task
     * @phpstan-param class-string<T> $className
     */
    public function setRootTask(string $className, mixed ...$args): void
    {
        $this->task = $this->createTask($className, ...$args);
    }

    /**
     * @param array<int, array{task: class-string, args: array<int, mixed>, finished: bool}> $tasks
     */
    public function restoreFromArray(array $tasks): void
    {
        $taskReflection = new \ReflectionClass(Task::class);
        $finishedProperty = $taskReflection->getProperty('finished');

        // @formatter:off
        $tasks = array_map(function (array $task) use ($finishedProperty) {
            $instance = $this->createTask($task['task'], ...$task['args']);
            $finishedProperty->setValue($instance, $task['finished']);
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

    public function execute(): void
    {
        foreach ($this->taskIterator() as $task) {
            if (!$task->finished) {
                $this->executeTask($task);

                if (!$task->finished) {
                    break;
                }
            }
        }
    }

    abstract protected function executeTask(Task $task): void;

    public function __toString(): string
    {
        $output = '';

        foreach ($this->taskIterator() as $task) {
            $output .= $task.PHP_EOL;
        }

        return $output;
    }

    /**
     * @return array<int, array{task: class-string, args: array<int, mixed>, finished: bool}>
     */
    public function jsonSerialize(): array
    {
        $tasks = [];

        foreach ($this->taskIterator() as $task) {
            $tasks[] = $task;
        }

        return $tasks;
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
