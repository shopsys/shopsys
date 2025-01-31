<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\ArrayUtils;

class ArrayHelper
{
    /**
     * @param array $array1
     * @param array $array2
     * @return bool
     */
    public static function haveArraysDifferentValues(array $array1, array $array2): bool
    {
        return array_diff($array1, $array2) !== [] || array_diff($array2, $array1) !== [];
    }
}
