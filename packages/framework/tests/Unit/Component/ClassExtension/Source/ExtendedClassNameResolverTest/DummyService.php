<?php

declare(strict_types=1);

namespace Tests\FrameworkBundle\Unit\Component\ClassExtension\Source\ExtendedClassNameResolverTest;

class DummyService
{
    public static function describe(): string
    {
        return 'framework';
    }
}
