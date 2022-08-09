<?php

namespace Shopsys\FrameworkBundle\Component\Utils;

class Utils
{
    /**
     * @template VariableType
     * @template DefaultType
     * @param VariableType|null $testVariable
     * @param DefaultType $default
     * @return VariableType|DefaultType
     */
    public static function ifNull(mixed $testVariable, mixed $default): mixed
    {
        return $testVariable !== null ? $testVariable : $default;
    }

    /**
     * @param mixed[] $array
     * @param string|int $key
     * @param mixed $defaultValue
     */
    public static function setArrayDefaultValue(array &$array, string|int $key, mixed $defaultValue = null): void
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
    public static function getArrayValue(array $array, string|int $key, mixed $defaultValue = null): mixed
    {
        return array_key_exists($key, $array) ? $array[$key] : $defaultValue;
    }

    /**
     * @param mixed $value
     * @return mixed[]
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
