<?php

declare(strict_types=1);

namespace App\Storage;

use Symfony\Component\DependencyInjection\Attribute\Autowire;

class AgentStorage
{
    private readonly string $savePath;
    private array $agents;

    public function __construct(
        #[Autowire('%kernel.project_dir%')]
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

    public function getAgents(): array
    {
        $this->load();

        return $this->agents;
    }

    private function setup(): void
    {
        if (!is_dir($this->projectDir.'/var/space-trader')) {
            mkdir($this->projectDir.'/var/space-trader');
        }
    }

    private function load(): void
    {
        $this->agents = json_decode(file_get_contents($this->savePath), true);
    }

    private function save(): void
    {
        file_put_contents($this->savePath, json_encode($this->agents, JSON_PRETTY_PRINT));
    }
}
