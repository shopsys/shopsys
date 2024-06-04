<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Bytes;

use function intval;

class BytesHelper
{
    /**
     * @return int
     */
    public static function getPhpMemoryLimitInBytes(): int
    {
        $memoryLimit = ini_get('memory_limit');

        if ($memoryLimit === '-1') {
            return -1;
        }

        return self::convertPhpStringByteDefinitionToBytes($memoryLimit);
    }

    /**
     * @param string $memoryLimit
     * @return int
     */
    public static function convertPhpStringByteDefinitionToBytes(string $memoryLimit): int
    {
        $memoryLimit = strtolower($memoryLimit);
        $max = ltrim($memoryLimit, '+');

        if (str_starts_with($max, '0x')) {
            $max = intval($max, 16);
        } elseif (str_starts_with($max, '0')) {
            $max = intval($max, 8);
        } else {
            $max = (int)$max;
        }

        switch (substr(rtrim($memoryLimit, 'b'), -1)) {
            case 't':
                $max *= 1024;
                // no break
            case 'g':
                $max *= 1024;
                // no break
            case 'm':
                $max *= 1024;
                // no break
            case 'k':
                $max *= 1024;
        }

        return $max;
    }
}
