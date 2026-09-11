<?php

declare(strict_types=1);

namespace Tests\FrameworkBundle\Unit\Component\ClassExtension;

use Override;
use PHPUnit\Framework\TestCase;
use Shopsys\FrameworkBundle\Component\ClassExtension\Exception\ExtendedClassNamesAlreadySetException;
use Shopsys\FrameworkBundle\Component\ClassExtension\ExtendedClassNameResolver;
use Tests\FrameworkBundle\Unit\Component\ClassExtension\Source\ExtendedClassNameResolverTest\DummyExtendedService;
use Tests\FrameworkBundle\Unit\Component\ClassExtension\Source\ExtendedClassNameResolverTest\DummyService;

class ExtendedClassNameResolverTest extends TestCase
{
    #[Override]
    protected function setUp(): void
    {
        parent::setUp();

        ExtendedClassNameResolver::reset();
    }

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

    public function testExtendedClassNamesCannotBeReplacedOnceSet(): void
    {
        ExtendedClassNameResolver::setExtendedClassNamesByClassName([]);

        $this->expectException(ExtendedClassNamesAlreadySetException::class);

        ExtendedClassNameResolver::setExtendedClassNamesByClassName([DummyService::class => DummyExtendedService::class]);
    }

    public function testResolvesToItselfBeforeTheMapIsSet(): void
    {
        $this->assertSame(DummyService::class, ExtendedClassNameResolver::resolve(DummyService::class));
    }
}
