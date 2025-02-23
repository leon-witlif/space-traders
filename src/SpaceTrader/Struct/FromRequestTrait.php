<?php

declare(strict_types=1);

namespace App\SpaceTrader\Struct;

trait FromRequestTrait
{
    public static function fromResponse(array $data): self
    {
        $reflection = new \ReflectionClass(self::class);
        $arguments = [];

        foreach ($reflection->getProperties() as $property) {
            if ($property->getType()->allowsNull()) {
                $arguments[] = array_key_exists($property->getName(), $data) ? $data[$property->getName()] : null;
            } else {
                $arguments[] = $data[$property->getName()];
            }
        }

        return $reflection->newInstance(...$arguments);
    }
}
