<?php

declare(strict_types=1);

namespace Asaas\Sdk\Model\Generated;

use Asaas\Sdk\Model\Contracts\ArraySerializable;
use Asaas\Sdk\Util\Serializer;

final class {{className}} implements ArraySerializable
{
{{properties}}

    public function __construct({{constructorSignature}})
    {
{{constructorBody}}
    }

    public function toArray(): array
    {
        return Serializer::toArray($this);
    }

    public static function fromArray(array $data): self
    {
        return Serializer::fromArray(self::class, $data);
    }
}
