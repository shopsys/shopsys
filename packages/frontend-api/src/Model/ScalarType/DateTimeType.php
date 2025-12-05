<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\ScalarType;

use DateTimeInterface;
use GraphQL\Language\AST\StringValueNode;
use Symfony\Component\Clock\DatePoint;

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
     * @return \Symfony\Component\Clock\DatePoint
     */
    public static function parseValue(string $value): DatePoint
    {
        return new DatePoint($value);
    }

    /**
     * @param \GraphQL\Language\AST\StringValueNode $valueNode
     * @return \Symfony\Component\Clock\DatePoint
     */
    public static function parseLiteral(StringValueNode $valueNode): DatePoint
    {
        return new DatePoint($valueNode->value);
    }
}
