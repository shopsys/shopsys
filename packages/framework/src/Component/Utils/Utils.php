<?php

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
     * @param string|int $key
     * @param mixed $defaultValue
     */
    public static function setArrayDefaultValue(array &$array, $key, $defaultValue = null): void
    {
        if (!array_key_exists($key, $array)) {
            $array[$key] = $defaultValue;
        }
    }

    /**
     * @param string|int $key
     * @param mixed $defaultValue
     * @return mixed
     */
    public static function getArrayValue(array $array, $key, $defaultValue = null)
    {
        return array_key_exists($key, $array) ? $array[$key] : $defaultValue;
    }

    /**
     * @param mixed $value
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
