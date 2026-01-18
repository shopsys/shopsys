<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\ArrayUtils;

class ArrayHelper
{
    /**
     * @param array<mixed> $array1
     * @param array<mixed> $array2
     */
    public static function haveArraysDifferentValues(array $array1, array $array2): bool
    {
        return array_diff($array1, $array2) !== [] || array_diff($array2, $array1) !== [];
    }
}
