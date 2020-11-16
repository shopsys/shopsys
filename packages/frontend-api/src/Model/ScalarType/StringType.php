<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\ScalarType;

use GraphQL\Type\Definition\StringType as BaseStringType;

class StringType extends BaseStringType
{
    /**
     * @param \GraphQL\Language\AST\Node $valueNode
     * @param array|null $variables
     * @return string|null
     */
    public function parseLiteral($valueNode, ?array $variables = null)
    {
        $value = parent::parseLiteral($valueNode, $variables);
        if ($value === null) {
            return null;
        }
        return trim($value);
    }

    /**
     * @param mixed $value
     * @return string
     */
    public function parseValue($value)
    {
        return trim(parent::parseValue($value));
    }
}
