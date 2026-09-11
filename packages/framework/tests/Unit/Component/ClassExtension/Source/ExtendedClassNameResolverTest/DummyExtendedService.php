<?php

declare(strict_types=1);

namespace Tests\FrameworkBundle\Unit\Component\ClassExtension\Source\ExtendedClassNameResolverTest;

use Override;

class DummyExtendedService extends DummyService
{
    #[Override]
    public static function describe(): string
    {
        return 'project';
    }
}
