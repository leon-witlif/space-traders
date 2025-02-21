<?php

declare(strict_types=1);

namespace App\Helper;

class Navigation
{
    public static function getSector(string $symbol): string
    {
        $parts = explode('-', $symbol);

        return $parts[0];
    }

    public static function getSystem(string $symbol): string
    {
        $parts = explode('-', $symbol);

        return $parts[0].'-'.$parts[1];
    }

    public static function getWaypoint(string $symbol): string
    {
        $parts = explode('-', $symbol);

        return $parts[0].'-'.$parts[1].'-'.$parts[2];
    }
}
