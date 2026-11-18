<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\ArrayUtils;

use Collator;

class ArraySorterHelper
{
    public function sortArrayAlphabeticallyByValue(string $valueKey, array &$array, string $locale): void
    {
        $collator = new Collator($locale);

        usort($array, static function ($a, $b) use ($valueKey, $collator) {
            return $collator->compare($a[$valueKey], $b[$valueKey]);
        });
    }
}
