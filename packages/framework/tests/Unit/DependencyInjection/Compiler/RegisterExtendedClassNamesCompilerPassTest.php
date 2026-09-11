<?php

declare(strict_types=1);

namespace Tests\FrameworkBundle\Unit\DependencyInjection\Compiler;

use PHPUnit\Framework\TestCase;
use Shopsys\FrameworkBundle\DependencyInjection\Compiler\RegisterExtendedClassNamesCompilerPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Tests\FrameworkBundle\Unit\Component\ClassExtension\Source\ExtendedClassNameResolverTest\DummyExtendedService;
use Tests\FrameworkBundle\Unit\Component\ClassExtension\Source\ExtendedClassNameResolverTest\DummyService;
use Tests\FrameworkBundle\Unit\Component\ClassExtension\Source\ExtendedClassNameResolverTest\DummyUnrelatedService;

class RegisterExtendedClassNamesCompilerPassTest extends TestCase
{
    public function testServiceResolvedToItsOwnClassIsNotMapped(): void
    {
        $container = $this->createContainer();
        $container->register(DummyService::class, DummyService::class)->setPublic(true);

        $container->compile();

        $this->assertSame([], $container->getParameter(RegisterExtendedClassNamesCompilerPass::EXTENDED_CLASS_NAMES_PARAMETER));
    }

    public function testAliasToExtendingClassIsMapped(): void
    {
        $container = $this->createContainer();
        $container->register(DummyExtendedService::class, DummyExtendedService::class)->setPublic(true);
        $container->setAlias(DummyService::class, DummyExtendedService::class);

        $container->compile();

        $this->assertSame(
            [DummyService::class => DummyExtendedService::class],
            $container->getParameter(RegisterExtendedClassNamesCompilerPass::EXTENDED_CLASS_NAMES_PARAMETER),
        );
    }

    public function testDefinitionWithExtendingClassIsMapped(): void
    {
        $container = $this->createContainer();
        $container->register(DummyService::class, DummyExtendedService::class)->setPublic(true);

        $container->compile();

        $this->assertSame(
            [DummyService::class => DummyExtendedService::class],
            $container->getParameter(RegisterExtendedClassNamesCompilerPass::EXTENDED_CLASS_NAMES_PARAMETER),
        );
    }

    public function testAliasToUnrelatedClassIsNotMapped(): void
    {
        $container = $this->createContainer();
        $container->register(DummyUnrelatedService::class, DummyUnrelatedService::class)->setPublic(true);
        $container->setAlias(DummyService::class, DummyUnrelatedService::class);

        $container->compile();

        $this->assertSame([], $container->getParameter(RegisterExtendedClassNamesCompilerPass::EXTENDED_CLASS_NAMES_PARAMETER));
    }

    public function testEntityExtensionMapIsIncluded(): void
    {
        $container = $this->createContainer();
        $container->setParameter(
            RegisterExtendedClassNamesCompilerPass::ENTITY_EXTENSION_MAP_PARAMETER,
            [DummyService::class => DummyExtendedService::class],
        );
        $container->register(DummyUnrelatedService::class, DummyUnrelatedService::class)->setPublic(true);

        $container->compile();

        $this->assertSame(
            [DummyService::class => DummyExtendedService::class],
            $container->getParameter(RegisterExtendedClassNamesCompilerPass::EXTENDED_CLASS_NAMES_PARAMETER),
        );
    }

    private function createContainer(): ContainerBuilder
    {
        $container = new ContainerBuilder();
        $container->addCompilerPass(new RegisterExtendedClassNamesCompilerPass());

        return $container;
    }
}
