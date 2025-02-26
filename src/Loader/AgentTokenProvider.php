<?php

declare(strict_types=1);

namespace App\Loader;

use Symfony\Component\HttpFoundation\RequestStack;

class AgentTokenProvider
{
    public function __construct(private readonly RequestStack $requestStack)
    {
    }

    public function getAgentToken(): string
    {
        return $this->requestStack->getCurrentRequest()->getSession()->get('agentToken');
    }
}
