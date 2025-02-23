<?php

declare(strict_types=1);

namespace App\Storage;

class WaypointStorage extends Storage
{
    public function __construct(private readonly string $projectDir)
    {
        parent::__construct($this->projectDir.'/var/space-trader/', 'waypoints.json');
    }

    /**
     * @param array{waypointSymbol: string, scanned: bool, type?: string, x?: int, y?: int, traits?: array<string>, factionSymbol?: string, exports?: array<string>, exchange?: array<string>} $data
     */
    public function add(array $data): void
    {
        $this->load();

        $this->data[] = $data;

        $this->save();
    }

    public function update(int $key, array $data): void
    {
        $this->load();

        $this->data[$key] = $data;

        $this->save();
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
     * @return array<array{waypointSymbol: string, scanned: bool, type?: string, x?: int, y?: int, traits?: array<string>, factionSymbol?: string, exports?: array<string>, exchange?: array<string>}>
     */
    public function list(): array
    {
        $this->load();

        return $this->data;
    }

    /**
     * @return array{waypointSymbol: string, scanned: bool, type?: string, x?: int, y?: int, traits?: array<string>, factionSymbol?: string, exports?: array<string>, exchange?: array<string>}|null
     */
    public function get(string $symbol): ?array
    {
        return array_find($this->list(), fn (array $market) => $market['waypointSymbol'] === $symbol);
    }

    public function key(string $symbol): int
    {
        return array_find_key($this->list(), fn (array $market) => $market['waypointSymbol'] === $symbol);
    }
}
