<?php

declare(strict_types=1);

namespace Asaas\Sdk\Util;

use Asaas\Sdk\Exception\ValidationException;
use Asaas\Sdk\Model\Contracts\ArraySerializable;

final class Serializer
{
    /**
     * @return array<string, mixed>
     */
    public static function toArray(object $dto): array
    {
        $data = [];
        foreach (get_object_vars($dto) as $key => $value) {
            $data[$key] = self::normalizeValue($value);
        }

        return $data;
    }

    /**
     * @template T of object
     * @param class-string<T> $class
     * @param array<string, mixed> $data
     * @return T
     */
    public static function fromArray(string $class, array $data): object
    {
        $reflection = new \ReflectionClass($class);
        $constructor = $reflection->getConstructor();

        if ($constructor === null) {
            return $reflection->newInstance();
        }

        $args = [];
        foreach ($constructor->getParameters() as $parameter) {
            $name = $parameter->getName();
            $args[] = $data[$name] ?? $parameter->getDefaultValue();
        }

        return $reflection->newInstanceArgs($args);
    }

    private static function normalizeValue(mixed $value): mixed
    {
        if ($value instanceof \BackedEnum) {
            return $value->value;
        }

        if ($value instanceof ArraySerializable) {
            return $value->toArray();
        }

        if ($value instanceof \DateTimeInterface) {
            return $value->format(\DateTimeInterface::ATOM);
        }

        if (is_array($value)) {
            return array_map(static fn($item) => self::normalizeValue($item), $value);
        }

        return $value;
    }

    /**
     * @param list<string> $required
     * @param array<string, mixed> $data
     */
    public static function validateRequired(array $required, array $data): void
    {
        foreach ($required as $field) {
            if (!array_key_exists($field, $data) || $data[$field] === null) {
                throw new ValidationException(sprintf('Campo obrigatório ausente: %s', $field));
            }
        }
    }
}
