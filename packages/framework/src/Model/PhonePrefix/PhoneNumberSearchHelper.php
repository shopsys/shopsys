<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\PhonePrefix;

class PhoneNumberSearchHelper
{
    public static function getDqlExpression(string $alias, string $fieldBase = 'telephone'): string
    {
        return sprintf(
            "CONCAT(COALESCE(%s.%sPrefix, ''), COALESCE(%s.%sNumber, ''))",
            $alias,
            $fieldBase,
            $alias,
            $fieldBase,
        );
    }
}
