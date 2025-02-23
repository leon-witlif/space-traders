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
                $arguments[] = array_key_exists($property->getName(), $data) ? static::createArgument($property, $data[$property->getName()]) : null;
            } else {
                $arguments[] = static::createArgument($property, $data[$property->getName()]);
            }
        }

        return $reflection->newInstanceArgs($arguments);
    }

    private static function createArgument(\ReflectionProperty $property, mixed $data): mixed
    {
        if (class_exists($property->getType()->getName())) {
            /** @var object&FromRequestTrait $class */
            $class = $property->getType()->getName();

            return $class::fromResponse($data);
        }

        return $data;
    }
}
