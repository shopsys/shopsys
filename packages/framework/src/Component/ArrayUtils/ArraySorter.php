<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\ArrayUtils;

use Collator;

class ArraySorter
{
    /**
     * @param string $valueKey
     * @param array $array
     * @param string $locale
     */
    public static function sortArrayAlphabeticallyByValue(string $valueKey, array &$array, string $locale): void
    {
        $collator = new Collator($locale);

        usort($array, static function ($a, $b) use ($valueKey, $collator) {
            return (int)($collator->getSortKey($a[$valueKey]) >= $collator->getSortKey($b[$valueKey]));
        });
    }
}
