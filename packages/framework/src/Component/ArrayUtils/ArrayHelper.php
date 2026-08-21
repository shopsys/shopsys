<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\ArrayUtils;

class ArrayHelper
{
    public static function haveArraysDifferentValues(array $array1, array $array2): bool
    {
        return array_diff($array1, $array2) !== [] || array_diff($array2, $array1) !== [];
    }

    public static function getStringOrNull(array $data, string $key): ?string
    {
        return self::normalizeStringOrNull($data[$key] ?? null);
    }

    public static function getArrayOrEmpty(array $data, string $key): array
    {
        return is_array($data[$key] ?? null) ? $data[$key] : [];
    }

    /**
     * Returns the keys that are present in both arrays.
     *
     * @param array<array-key, mixed> $array1
     * @param array<array-key, mixed> $array2
     * @return list<int|string>
     */
    public static function getCommonKeys(array $array1, array $array2): array
    {
        return array_keys(array_intersect_key($array1, $array2));
    }

    protected static function normalizeStringOrNull(mixed $value): ?string
    {
        if (!is_string($value) || $value === '') {
            return null;
        }

        return $value;
    }
}
