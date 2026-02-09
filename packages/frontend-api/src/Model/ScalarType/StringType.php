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
use Override;
use Shopsys\FrameworkBundle\Component\String\TransformStringHelper;
use function is_object;
use function is_scalar;
use function is_string;
use function method_exists;

class StringType extends ScalarType
{
    /**
     * @param mixed $value
     */
    #[Override]
    public function serialize($value): ?string
    {
        $canCast = is_scalar($value)
            || (is_object($value) && method_exists($value, '__toString'))
            || $value === null;

        if (!$canCast) {
            $notStringable = Utils::printSafe($value);

            throw new SerializationError("String cannot represent value: {$notStringable}");
        }

        return TransformStringHelper::getTrimmedStringOrNullOnEmpty((string)$value);
    }

    /**
     * @param mixed $value
     * @throws \GraphQL\Error\Error
     */
    #[Override]
    public function parseValue($value): ?string
    {
        if (!is_string($value)) {
            $notString = Utils::printSafeJson($value);

            throw new Error("String cannot represent a non string value: {$notString}");
        }

        return TransformStringHelper::getTrimmedStringOrNullOnEmpty($value);
    }

    #[Override]
    public function parseLiteral(Node $valueNode, ?array $variables = null): ?string
    {
        if ($valueNode instanceof StringValueNode) {
            return TransformStringHelper::getTrimmedStringOrNullOnEmpty($valueNode->value);
        }

        $notString = Printer::doPrint($valueNode);

        throw new Error("String cannot represent a non string value: {$notString}", $valueNode);
    }
}
