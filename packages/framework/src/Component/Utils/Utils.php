<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Utils;

class Utils
{
    /**
     * @param mixed $testVariable
     * @param mixed $default
     * @return mixed
     */
    public static function ifNull(mixed $testVariable, mixed $default): mixed
    {
        return $testVariable ?? $default;
    }

    /**
     * @param array $array
     * @param int|string $key
     * @param mixed|null $defaultValue
     */
    public static function setArrayDefaultValue(array &$array, int|string $key, mixed $defaultValue = null): void
    {
        if (!array_key_exists($key, $array)) {
            $array[$key] = $defaultValue;
        }
    }

    /**
     * @param mixed $value
     * @return array
     */
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
