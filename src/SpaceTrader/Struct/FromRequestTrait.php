<?php

declare(strict_types=1);

namespace App\SpaceTrader\Struct;

trait FromRequestTrait
{
    /**
     * @phpstan-param array<string, mixed> $data
     */
    public static function fromResponse(array $data): self
    {
        $reflection = new \ReflectionClass(self::class);
        $arguments = [];

        foreach ($reflection->getProperties() as $property) {
            if ($property->getType()->allowsNull()) {
                $arguments[] = array_key_exists($property->getName(), $data) ? self::createArgument($property, $data[$property->getName()]) : null;
            } else {
                $arguments[] = self::createArgument($property, $data[$property->getName()]);
            }
        }

        return $reflection->newInstanceArgs($arguments);
    }

    private static function createArgument(\ReflectionProperty $property, mixed $data): mixed
    {
        /** @var \ReflectionNamedType|\ReflectionUnionType|\ReflectionIntersectionType|null $type */
        $type = $property->getType();
        $isArray = false;

        if ($type->getName() === 'array' && $property->getDocComment()) {
            preg_match('/array<([a-z]+)>/i', $property->getDocComment(), $matches);

            if (array_key_exists(1, $matches)) {
                $isArray = true;

                $type = new class ($matches[1]) {
                    // @formatter:off
                    public function __construct(public readonly string $name) {}
                    /** @return class-string */
                    public function getName(): string { return 'App\\SpaceTrader\\Struct\\'.$this->name; }
                    // @formmatter:on
                };
            }
        }

        if (class_exists($type->getName())) {
            $class = $type->getName();

            return $isArray
                ? array_map(fn (array $arguments) => $class::fromResponse($arguments), $data)
                : $class::fromResponse($data);
        }

        return $data;
    }
}
