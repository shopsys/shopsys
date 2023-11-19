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
    public static function ifNull($testVariable, $default)
    {
        return $testVariable !== null ? $testVariable : $default;
    }

    /**
     * @param mixed[] $array
     * @param string|int $key
     * @param mixed $defaultValue
     */
    public static function setArrayDefaultValue(&$array, $key, $defaultValue = null): void
    {
        if (!array_key_exists($key, $array)) {
            $array[$key] = $defaultValue;
        }
    }

    /**
     * @param mixed[] $array
     * @param string|int $key
     * @param mixed $defaultValue
     * @return mixed
     */
    public static function getArrayValue($array, $key, $defaultValue = null)
    {
        return array_key_exists($key, $array) ? $array[$key] : $defaultValue;
    }

    /**
     * @param mixed $value
     * @return mixed[]
     */
    public static function mixedToArray($value): array
    {
        if ($value === null) {
            $value = [];
        } elseif (!is_array($value)) {
            $value = [$value];
        }

        return $value;
    }
}
