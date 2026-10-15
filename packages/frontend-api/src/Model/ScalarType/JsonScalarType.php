<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\ScalarType;

use GraphQL\Language\AST\Node;
use InvalidArgumentException;

class JsonScalarType
{
    public static function serialize(mixed $value): mixed
    {
        return $value;
    }

    public static function parseValue(mixed $value): never
    {
        throw new InvalidArgumentException('The JSON scalar is output only');
    }

    public static function parseLiteral(Node $valueNode): never
    {
        throw new InvalidArgumentException('The JSON scalar is output only');
    }
}
