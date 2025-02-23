<?php

declare(strict_types=1);

namespace App\Storage;

abstract class Storage implements StorageInterface
{
    private readonly string $savePath;
    private readonly string $saveFile;

    protected array $data;

    public function __construct(string $savePath, string $saveFile)
    {
        $this->savePath = $savePath;
        $this->saveFile = $saveFile;

        $this->setup();
    }

    private function setup(): void
    {
        if (!is_dir($this->savePath)) {
            mkdir($this->savePath);
        }

        if (!is_file($this->savePath.$this->saveFile)) {
            touch($this->savePath.$this->saveFile);
        }
    }

    protected function load(): void
    {
        $this->data = json_decode(file_get_contents($this->savePath.$this->saveFile), true) ?? [];
    }

    protected function save(): void
    {
        file_put_contents($this->savePath.$this->saveFile, json_encode($this->data, JSON_PRETTY_PRINT));
    }
}
