<?php

namespace Shopsys\FrameworkBundle\Component;

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
     * @param array $array
     * @param string|int $key
     * @param mixed $defaultValue
     */
    public static function setArrayDefaultValue(&$array, $key, $defaultValue = null)
    {
        if (!array_key_exists($key, $array)) {
            $array[$key] = $defaultValue;
        }
    }

    /**
     * @param array $array
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
     * @return array
     */
    public static function mixedToArray($value)
    {
        if ($value === null) {
            $value = [];
        } elseif (!is_array($value)) {
            $value = [$value];
        }

        return $value;
    }
}
