<?php

declare(strict_types=1);

namespace Playground\Utils;

use ReflectionClass;
use ReflectionNamedType;

final class ArgumentHydrator
{
    /**
     * @param array<string, mixed> $data
     */
    public function hydrate(string $className, array $data): object
    {
        if (!class_exists($className)) {
            throw new \RuntimeException("Classe não encontrada: {$className}");
        }

        $reflection = new ReflectionClass($className);
        $instance = $this->instantiate($reflection, $data);

        foreach ($data as $key => $value) {
            if ($reflection->hasProperty($key)) {
                $property = $reflection->getProperty($key);
                if ($property->isPublic()) {
                    $property->setValue($instance, $this->hydrateValue($property->getType(), $value));
                    continue;
                }
            }

            $setter = 'set' . ucfirst((string) $key);
            if ($reflection->hasMethod($setter)) {
                $method = $reflection->getMethod($setter);
                if ($method->isPublic()) {
                    $parameter = $method->getParameters()[0] ?? null;
                    $argument = $parameter ? $this->hydrateValue($parameter->getType(), $value) : $value;
                    $method->invoke($instance, $argument);
                }
            }
        }

        return $instance;
    }

    /**
     * @param array<string, mixed> $data
     */
    private function instantiate(ReflectionClass $reflection, array $data): object
    {
        $constructor = $reflection->getConstructor();
        if ($constructor === null || $constructor->getNumberOfParameters() === 0) {
            return $reflection->newInstance();
        }

        $args = [];
        foreach ($constructor->getParameters() as $parameter) {
            $name = $parameter->getName();
            if (array_key_exists($name, $data)) {
                $args[] = $this->hydrateValue($parameter->getType(), $data[$name]);
                continue;
            }

            if ($parameter->isDefaultValueAvailable()) {
                $args[] = $parameter->getDefaultValue();
                continue;
            }

            throw new \RuntimeException("Parâmetro obrigatório ausente: {$name}");
        }

        return $reflection->newInstanceArgs($args);
    }

    private function hydrateValue(?\ReflectionType $type, mixed $value): mixed
    {
        if ($type instanceof ReflectionNamedType && !$type->isBuiltin()) {
            $className = $type->getName();
            if (is_array($value)) {
                return $this->hydrate($className, $value);
            }
        }

        if (is_array($value)) {
            return array_map(function ($item) use ($type) {
                if ($item instanceof \stdClass) {
                    return (array) $item;
                }

                return $item;
            }, $value);
        }

        return $value;
    }
}
