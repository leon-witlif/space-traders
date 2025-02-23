<?php

declare(strict_types=1);

namespace App\Storage;

class AgentStorage extends Storage
{
    public function __construct(private readonly string $projectDir)
    {
        parent::__construct($this->projectDir.'/var/space-trader/', 'agents.json');
    }

    /**
     * @param array{token: string, symbol: string} $data
     */
    public function add(array $data): void
    {
        $this->load();

        $this->data[] = $data;

        $this->save();
    }

    public function update(int $key, array $data): void
    {
        throw new \RuntimeException('NYI');
    }

    public function remove(int $key): void
    {
        throw new \RuntimeException('NYI');
    }

    public function clear(): void
    {
        throw new \RuntimeException('NYI');
    }

    /**
     * @return array<array{token: string, symbol: string}>
     */
    public function list(): array
    {
        $this->load();

        return $this->data;
    }

    /**
     * @return array{token: string, symbol: string}|null
     */
    public function get(string $symbol): ?array
    {
        return array_find($this->list(), fn (array $agent) => $agent['symbol'] === $symbol);
    }

    public function key(string $symbol): int
    {
        return array_find_key($this->list(), fn (array $agent) => $agent['symbol'] === $symbol);
    }
}
