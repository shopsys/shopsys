<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\ScalarType;

use DateTime;
use DateTimeInterface;
use GraphQL\Language\AST\StringValueNode;

class DateTimeType
{
    /**
     * @param \DateTimeInterface $value
     * @return string
     */
    public static function serialize(DateTimeInterface $value): string
    {
        return $value->format(DateTimeInterface::ATOM);
    }

    /**
     * @param string $value
     * @return \DateTime
     */
    public static function parseValue(string $value): DateTime
    {
        return new DateTime($value);
    }

    /**
     * @param \GraphQL\Language\AST\StringValueNode $valueNode
     * @return \DateTime
     */
    public static function parseLiteral(StringValueNode $valueNode): DateTime
    {
        return new DateTime($valueNode->value);
    }
}
