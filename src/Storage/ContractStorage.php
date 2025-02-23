<?php

declare(strict_types=1);

namespace App\Storage;

class ContractStorage extends Storage
{
    public function __construct(private readonly string $projectDir)
    {
        parent::__construct($this->projectDir.'/var/space-trader/', 'contracts.json');
    }

    /**
     * @param array{token: string, contractId: string, data: array} $data
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
        $this->load();

        array_splice($this->data, $key, 1);
        $this->data = array_values($this->data);

        $this->save();
    }

    public function clear(): void
    {
        throw new \RuntimeException('NYI');
    }

    /**
     * @return array<array{token: string, contractId: string, data: array}>
     */
    public function list(): array
    {
        $this->load();

        return $this->data;
    }

    /**
     * @return array{token: string, contractId: string, data: array}
     */
    public function get(string $symbol): array
    {
        return array_find($this->list(), fn (array $contract) => $contract['contractId'] === $symbol);
    }

    public function key(string $symbol): int
    {
        return array_find_key($this->list(), fn (array $contract) => $contract['contractId'] === $symbol);
    }
}
