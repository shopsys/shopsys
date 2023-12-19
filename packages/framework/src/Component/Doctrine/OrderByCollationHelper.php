<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Doctrine;

class OrderByCollationHelper
{
    /**
     * @param string $sort
     * @param string $locale
     * @return string
     */
    public static function createOrderByForLocale(string $sort, string $locale): string
    {
        $collation = self::getCollationByLocale($locale);

        return 'COLLATE(' . $sort . ", '" . $collation . "')";
    }

    /**
     * @param string $locale
     * @return string
     */
    public static function getCollationByLocale(string $locale): string
    {
        return $locale . '-x-icu';
    }
}
