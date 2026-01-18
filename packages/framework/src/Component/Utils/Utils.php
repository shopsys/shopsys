<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Utils;

class Utils
{
    /**
     * @param string[] $needles
     */
    public static function strStartsWithAny(string $haystack, array $needles): bool
    {
        foreach ($needles as $needle) {
            if (str_starts_with($haystack, $needle)) {
                return true;
            }
        }

        return false;
    }

    public static function ifNull(mixed $testVariable, mixed $default): mixed
    {
        return $testVariable ?? $default;
    }

    /**
     * @param array<int|string, mixed> $array
     */
    public static function setArrayDefaultValue(array &$array, int|string $key, mixed $defaultValue = null): void
    {
        if (!array_key_exists($key, $array)) {
            $array[$key] = $defaultValue;
        }
    }

    public static function mixedToArray(mixed $value): array
    {
        if ($value === null) {
            $value = [];
        } elseif (!is_array($value)) {
            $value = [$value];
        }

        return $value;
    }
}
