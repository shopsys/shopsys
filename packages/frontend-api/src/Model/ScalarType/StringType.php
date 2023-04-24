<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\ScalarType;

use GraphQL\Language\AST\Node;
use GraphQL\Type\Definition\StringType as BaseStringType;
use Shopsys\FrameworkBundle\Component\String\TransformString;

class StringType extends BaseStringType
{
    /**
     * webonix/graphql-php has wrong typing between StringType::parseLiteral and Leaf type interface::parseLiteral,
     * so phpstan-param annotation must be used
     *
     * @phpstan-param \GraphQL\Language\AST\IntValueNode|\GraphQL\Language\AST\FloatValueNode|\GraphQL\Language\AST\StringValueNode|\GraphQL\Language\AST\BooleanValueNode|\GraphQL\Language\AST\NullValueNode $valueNode
     * @param \GraphQL\Language\AST\Node $valueNode
     * @param array|null $variables
     * @return string|null
     */
    public function parseLiteral(Node $valueNode, ?array $variables = null)
    {
        $value = parent::parseLiteral($valueNode, $variables);

        return TransformString::getTrimmedStringOrNullOnEmpty($value);
    }

    /**
     * @param mixed $value
     * @return string|null
     */
    public function parseValue($value)
    {
        $value = parent::parseValue($value);

        return TransformString::getTrimmedStringOrNullOnEmpty($value);
    }
}
