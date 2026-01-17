<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\ScalarType;

use GraphQL\Language\AST\StringValueNode;

class PasswordType
{
    public static function serialize(string $value): string
    {
        return $value;
    }

    public static function parseValue(string $value): string
    {
        return $value;
    }

    public static function parseLiteral(StringValueNode $valueNode): string
    {
        return $valueNode->value;
    }
}
