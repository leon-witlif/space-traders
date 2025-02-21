<?php

declare(strict_types=1);

namespace App\Storage;

class AgentStorage
{
    private readonly string $savePath;
    private array $agents;

    public function __construct(
        private readonly string $projectDir,
    ) {
        $this->savePath = $this->projectDir.'/var/space-trader/agents.json';

        $this->setup();
        $this->load();
    }

    public function addAgent(string $symbol, string $token): void
    {
        $this->agents[] = [
            'symbol' => $symbol,
            'token' => $token,
        ];

        $this->save();
    }

    /**
     * @return array<int, array{symbol: string, token: string}>
     */
    public function getAgents(): array
    {
        $this->load();

        return $this->agents;
    }

    /**
     * @return array{symbol: string, token: string}
     */
    public function getAgent(string $symbol): array
    {
        return array_find($this->getAgents(), fn (array $agent) => $agent['symbol'] === $symbol);
    }

    private function setup(): void
    {
        if (!is_dir($this->projectDir.'/var/space-trader')) {
            mkdir($this->projectDir.'/var/space-trader');
        }

        if (!is_file($this->savePath)) {
            touch($this->savePath);
        }
    }

    private function load(): void
    {
        $this->agents = json_decode(file_get_contents($this->savePath), true) ?? [];
    }

    private function save(): void
    {
        file_put_contents($this->savePath, json_encode($this->agents, JSON_PRETTY_PRINT));
    }
}
