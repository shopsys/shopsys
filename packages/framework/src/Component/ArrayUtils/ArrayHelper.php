<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\ArrayUtils;

class ArrayHelper
{
    public static function haveArraysDifferentValues(array $array1, array $array2): bool
    {
        return array_diff($array1, $array2) !== [] || array_diff($array2, $array1) !== [];
    }
}
