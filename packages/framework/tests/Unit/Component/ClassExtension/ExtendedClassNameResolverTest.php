<?php

declare(strict_types=1);

namespace Tests\FrameworkBundle\Unit\Component\ClassExtension;

use PHPUnit\Framework\TestCase;
use Shopsys\FrameworkBundle\Component\ClassExtension\ExtendedClassNameResolver;
use Tests\FrameworkBundle\Unit\Component\ClassExtension\Source\ExtendedClassNameResolverTest\DummyExtendedService;
use Tests\FrameworkBundle\Unit\Component\ClassExtension\Source\ExtendedClassNameResolverTest\DummyService;

class ExtendedClassNameResolverTest extends TestCase
{
    public function testUnknownClassNameResolvesToItself(): void
    {
        ExtendedClassNameResolver::setExtendedClassNamesByClassName([]);

        $this->assertSame(DummyService::class, ExtendedClassNameResolver::resolve(DummyService::class));
        $this->assertSame('framework', ExtendedClassNameResolver::resolve(DummyService::class)::describe());
    }

    public function testStaticCallOnResolvedClassNameLandsInExtendedClass(): void
    {
        ExtendedClassNameResolver::setExtendedClassNamesByClassName([
            DummyService::class => DummyExtendedService::class,
        ]);

        $this->assertSame(DummyExtendedService::class, ExtendedClassNameResolver::resolve(DummyService::class));
        $this->assertSame('project', ExtendedClassNameResolver::resolve(DummyService::class)::describe());
    }
}
