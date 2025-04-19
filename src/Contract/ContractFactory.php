<?php

declare(strict_types=1);

namespace App\Contract;

use App\SpaceTrader\ApiRegistry;

final class ContractFactory
{
    private readonly \ReflectionClass $parentReflection;
    private readonly \ReflectionMethod $parentConstructor;

    public function __construct(
        private readonly ApiRegistry $apiRegistry,
        private readonly TaskInitializer $taskInitializer,
    ) {
        $this->parentReflection = new \ReflectionClass(Contract::class);
        $this->parentConstructor = $this->parentReflection->getConstructor();
    }

    /**
     * @phpstan-param class-string<Contract> $classname
     */
    public function createContract(string $classname, mixed ...$args): Contract
    {
        $instance = new $classname(...$args);

        $this->parentConstructor->invoke($instance, $this->apiRegistry, $this->taskInitializer);

        return $instance;
    }
}
