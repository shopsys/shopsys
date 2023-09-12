<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\ScalarType;

use GraphQL\Error\Error;
use GraphQL\Error\SerializationError;
use GraphQL\Language\AST\Node;
use GraphQL\Language\AST\StringValueNode;
use GraphQL\Language\Printer;
use GraphQL\Type\Definition\ScalarType;
use GraphQL\Utils\Utils;
use Shopsys\FrameworkBundle\Component\String\TransformString;
use function is_object;
use function is_scalar;
use function is_string;
use function method_exists;

class StringType extends ScalarType
{
    /**
     * @param mixed $value
     * @return string|null
     */
    public function serialize($value): ?string
    {
        $canCast = is_scalar($value)
            || (is_object($value) && method_exists($value, '__toString'))
            || $value === null;

        if (!$canCast) {
            $notStringable = Utils::printSafe($value);

            throw new SerializationError("String cannot represent value: {$notStringable}");
        }

        return TransformString::getTrimmedStringOrNullOnEmpty((string)$value);
    }

    /**
     * @param mixed $value
     * @throws \GraphQL\Error\Error
     * @return string|null
     */
    public function parseValue($value): ?string
    {
        if (!is_string($value)) {
            $notString = Utils::printSafeJson($value);

            throw new Error("String cannot represent a non string value: {$notString}");
        }

        return TransformString::getTrimmedStringOrNullOnEmpty($value);
    }

    /**
     * @param \GraphQL\Language\AST\Node $valueNode
     * @param array|null $variables
     * @return string|null
     */
    public function parseLiteral(Node $valueNode, ?array $variables = null): ?string
    {
        if ($valueNode instanceof StringValueNode) {
            return TransformString::getTrimmedStringOrNullOnEmpty($valueNode->value);
        }

        $notString = Printer::doPrint($valueNode);

        throw new Error("String cannot represent a non string value: {$notString}", $valueNode);
    }
}
