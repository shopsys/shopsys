<?php

declare(strict_types=1);

namespace Shopsys\CodingStandards\Helper;

use Nette\Utils\Strings;

final class ShopsysFixerNaming
{
    /**
     * @param string $class
     * @return string
     */
    public static function createFromClass(string $class): string
    {
        $lastPart = Strings::after($class, '\\', -1);

        $fixerName = Strings::substring($lastPart, 0, -strlen('Fixer'));

        $fixerName = Strings::replace($fixerName, '#[A-Z]#', function (array $value): string {
            return '_' . strtolower($value[0]);
        });

        $fixerName = Strings::substring($fixerName, 1);

        return 'Shopsys/' . $fixerName;
    }
}
