<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Doctrine;

class OrderByCollationHelper
{
    public function createOrderByForLocale(string $sort, string $locale): string
    {
        $collation = $this->getCollationByLocale($locale);

        return 'COLLATE(' . $sort . ", '" . $collation . "')";
    }

    public function getCollationByLocale(string $locale): string
    {
        return $locale . '-x-icu';
    }
}
