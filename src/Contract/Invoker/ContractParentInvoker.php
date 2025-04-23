<?php

declare(strict_types=1);

namespace App\Contract\Invoker;

use App\Contract\Contract;
use App\SpaceTrader\ApiRegistry;

final class ContractParentInvoker
{
    /** @var \ReflectionClass<Contract> */
    private readonly \ReflectionClass $parentReflection;
    private readonly \ReflectionMethod $parentConstructor;

    public function __construct(
        private readonly ApiRegistry $apiRegistry,
        private readonly TaskParentInvoker $taskParentInvoker,
    ) {
        $this->parentReflection = new \ReflectionClass(Contract::class);
        $this->parentConstructor = $this->parentReflection->getConstructor();
    }

    public function invoke(Contract $contract): void
    {
        $this->parentConstructor->invoke($contract, $this->apiRegistry, $this->taskParentInvoker);
    }
}
