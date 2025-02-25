<?php

declare(strict_types=1);

namespace App\Storage;

/**
 * @phpstan-extends Storage<array{waypointSymbol: string, scanned: bool, type?: string, x?: int, y?: int, traits?: array<string>, factionSymbol?: string, exports?: array<string>, exchange?: array<string>}>
 */
class WaypointStorage extends Storage
{
    public function __construct(private readonly string $projectDir)
    {
        parent::__construct($this->projectDir.'/var/space-trader/', 'waypoints.json');
    }

    protected function getIndexKey(): string
    {
        return 'waypointSymbol';
    }
}
