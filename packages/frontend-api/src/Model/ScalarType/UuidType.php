<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\ScalarType;

use GraphQL\Language\AST\StringValueNode;
use InvalidArgumentException;
use Ramsey\Uuid\Uuid;

class UuidType
{
    /**
     * @param string $value
     * @return string
     */
    public static function serialize(string $value): string
    {
        return $value;
    }

    /**
     * @param string $value
     * @return string
     */
    public static function parseValue(string $value): string
    {
        if (!Uuid::isValid($value)) {
            throw new InvalidArgumentException(sprintf('"%s" is not valid UUID', $value));
        }

        return $value;
    }

    /**
     * @param \GraphQL\Language\AST\StringValueNode $valueNode
     * @return string
     */
    public static function parseLiteral(StringValueNode $valueNode): string
    {
        if (!Uuid::isValid($valueNode->value)) {
            throw new InvalidArgumentException(sprintf('"%s" is not valid UUID', $valueNode->value));
        }

        return $valueNode->value;
    }
}
