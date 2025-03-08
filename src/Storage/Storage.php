<?php

declare(strict_types=1);

namespace App\Storage;

/**
 * @phpstan-template T of array
 */
abstract class Storage
{
    /** @phpstan-var T */
    protected array $data;

    private readonly string $savePath;
    private readonly string $saveFile;

    public function __construct(string $savePath, string $saveFile)
    {
        $this->data = [];

        $this->savePath = $savePath;
        $this->saveFile = $saveFile;

        $this->setup();
    }

    /**
     * @phpstan-param T $data
     */
    public function add(array $data): void
    {
        $this->load();

        $this->data[] = $data;

        $this->save();
    }

    /**
     * @phpstan-param int $key
     * @phpstan-param T $data
     */
    public function update(int $key, array $data): void
    {
        $this->load();

        $this->data[$key] = $data;

        $this->save();
    }

    public function updateField(int $key, string $field, mixed $data): void
    {
        $this->load();

        $this->data[$key][$field] = $data;

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
     * @phpstan-return array<int, T>
     */
    public function list(): array
    {
        $this->load();

        return $this->data;
    }

    /**
     * @phpstan-return T|null
     */
    public function get(string $key): ?array
    {
        return array_find($this->list(), fn (array $entry) => $entry[$this->getIndexKey()] === $key);
    }

    public function key(string $key): int
    {
        return array_find_key($this->list(), fn (array $agent) => $agent[$this->getIndexKey()] === $key);
    }

    abstract protected function getIndexKey(): string;

    private function setup(): void
    {
        if (!is_dir($this->savePath)) {
            mkdir($this->savePath);
        }

        if (!is_file($this->savePath.$this->saveFile)) {
            touch($this->savePath.$this->saveFile);
        }
    }

    private function load(): void
    {
        $this->data = json_decode(file_get_contents($this->savePath.$this->saveFile), true) ?? [];
    }

    private function save(): void
    {
        file_put_contents($this->savePath.$this->saveFile, json_encode($this->data, JSON_PRETTY_PRINT));
    }
}
