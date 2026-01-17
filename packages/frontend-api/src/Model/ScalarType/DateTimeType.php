<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\ScalarType;

use DateTimeInterface;
use GraphQL\Language\AST\StringValueNode;
use Symfony\Component\Clock\DatePoint;

class DateTimeType
{
    public static function serialize(DateTimeInterface $value): string
    {
        return $value->format(DateTimeInterface::ATOM);
    }

    public static function parseValue(string $value): DatePoint
    {
        return new DatePoint($value);
    }

    public static function parseLiteral(StringValueNode $valueNode): DatePoint
    {
        return new DatePoint($valueNode->value);
    }
}
