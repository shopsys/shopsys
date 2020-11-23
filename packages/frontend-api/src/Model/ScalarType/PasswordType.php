<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\ScalarType;

use GraphQL\Language\AST\StringValueNode;

class PasswordType
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
        return $value;
    }

    /**
     * @param \GraphQL\Language\AST\StringValueNode $valueNode
     * @return string
     */
    public static function parseLiteral(StringValueNode $valueNode): string
    {
        return $valueNode->value;
    }
}
