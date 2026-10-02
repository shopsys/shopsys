<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Product;

class ProductFullNameDqlHelper
{
    /**
     * DQL counterpart of Product::getFullName(), so the full name can be selected from the database
     */
    public static function getDqlExpression(string $translationAlias): string
    {
        return sprintf(
            "TRIM(CONCAT(COALESCE(%s.namePrefix, ''), ' ', COALESCE(%s.name, ''), ' ', COALESCE(%s.nameSuffix, '')))",
            $translationAlias,
            $translationAlias,
            $translationAlias,
        );
    }
}
