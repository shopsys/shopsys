<?php

declare(strict_types=1);

namespace Tests\CodingStandards\Sniffs\ForceLateStaticBindingForProtectedConstantsSniff;

class SelfWithMethodsAndVariables
{
    public const A = 'value';
    protected const B = 'value';
    protected const string TYPED_B = 'typed_value';
    private const C = 'value';

    public static $publicProperty;

    protected static $protectedProperty;

    private static $privateProperty;

    public function method()
    {
        echo self::A;
        self::class;
        self::publicStaticMethod();
        self::protectedStaticMethod();
        self::privateStaticMethod();

        echo static::B;
        self::class;
        self::publicStaticMethod();
        self::protectedStaticMethod();
        self::privateStaticMethod();

        echo self::C;
        self::class;
        self::publicStaticMethod();
        self::protectedStaticMethod();
        self::privateStaticMethod();

        echo self::A;
        echo static::B;
        echo self::C;

        echo self::$publicProperty;
        echo self::$protectedProperty;
        echo self::$privateProperty;

        echo static::$publicProperty;
        echo static::$protectedProperty;
        echo static::$privateProperty;

        echo static::TYPED_B;
    }

    public static function publicStaticMethod()
    {
        echo 'value';
    }

    protected static function protectedStaticMethod()
    {
        echo 'value';
    }

    private static function privateStaticMethod()
    {
        echo 'value';
    }
}
