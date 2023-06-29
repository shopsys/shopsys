<?php

declare(strict_types=1);

namespace Tests\CodingStandards\Sniffs\ForceLateStaticBindingForProtectedConstantsSniff;

class SingleValue
{
    public const A = 'value';
    protected const B = 'value';
    private const C = 'value';
    const D = 'value';

    public function method()
    {
        echo self::A;
        echo self::B;
        echo self::C;
        echo self::D;

        echo static::A;
        echo static::B;
        echo static::C;
        echo static::D;
    }
}
