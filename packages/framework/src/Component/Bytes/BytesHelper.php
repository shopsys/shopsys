<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Bytes;

use function intval;

class BytesHelper
{
    public function getPhpMemoryLimitInBytes(): int
    {
        $memoryLimit = ini_get('memory_limit');

        if ($memoryLimit === '-1') {
            return -1;
        }

        return $this->convertPhpStringByteDefinitionToBytes($memoryLimit);
    }

    public function convertPhpStringByteDefinitionToBytes(string $memoryLimit): int
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

    public static function convertBytesToReadableString(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB'];
        $power = $bytes > 0 ? intval(log($bytes, 1024)) : 0;
        $power = min($power, count($units) - 1);

        return number_format($bytes / (1024 ** $power), 2) . ' ' . $units[$power];
    }
}
