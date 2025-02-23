<?php

declare(strict_types=1);

namespace App\Storage;

interface StorageInterface
{
    public function add(array $data): void;

    public function update(int $key, array $data): void;

    public function remove(int $key): void;

    public function clear(): void;

    public function list(): array;

    public function get(string $symbol): array;

    public function key(string $symbol): int;
}
