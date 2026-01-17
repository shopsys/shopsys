<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\ScalarType;

use GraphQL\Language\AST\StringValueNode;
use InvalidArgumentException;
use Ramsey\Uuid\Uuid;

class UuidType
{
    public static function serialize(string $value): string
    {
        return $value;
    }

    public static function parseValue(string $value): string
    {
        if (!Uuid::isValid($value)) {
            throw new InvalidArgumentException(sprintf('"%s" is not valid UUID', $value));
        }

        return $value;
    }

    public static function parseLiteral(StringValueNode $valueNode): string
    {
        if (!Uuid::isValid($valueNode->value)) {
            throw new InvalidArgumentException(sprintf('"%s" is not valid UUID', $valueNode->value));
        }

        return $valueNode->value;
    }
}
