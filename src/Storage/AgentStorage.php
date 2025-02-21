<?php

declare(strict_types=1);

namespace App\Storage;

class AgentStorage extends Storage
{
    public function __construct(
        private readonly string $projectDir,
    ) {
        parent::__construct($this->projectDir.'/var/space-trader/', 'agents.json');
    }

    public function addAgent(string $token, string $symbol): void
    {
        $this->data[] = [
            'token' => $token,
            'symbol' => $symbol,
        ];

        $this->save();
    }

    /**
     * @return array<int, array{token: string, symbol: string}>
     */
    public function getAgents(): array
    {
        $this->load();

        return $this->data;
    }

    /**
     * @return array{token: string, symbol: string}
     */
    public function getAgent(string $symbol): array
    {
        return array_find($this->getAgents(), fn (array $agent) => $agent['symbol'] === $symbol);
    }
}
