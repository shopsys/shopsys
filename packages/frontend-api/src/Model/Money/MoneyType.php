<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Money;

use GraphQL\Language\AST\StringValueNode;
use Shopsys\FrameworkBundle\Component\Money\Money;

class MoneyType
{
    /**
     * @param \Shopsys\FrameworkBundle\Component\Money\Money $value
     * @return string
     */
    public static function serialize(Money $value): string
    {
        return $value->getAmount();
    }

    /**
     * @param string $value
     * @return \Shopsys\FrameworkBundle\Component\Money\Money
     */
    public static function parseValue(string $value): Money
    {
        return Money::create($value);
    }

    /**
     * @param \GraphQL\Language\AST\StringValueNode $valueNode
     * @return \Shopsys\FrameworkBundle\Component\Money\Money
     */
    public static function parseLiteral(StringValueNode $valueNode): Money
    {
        return Money::create($valueNode->value);
    }
}
