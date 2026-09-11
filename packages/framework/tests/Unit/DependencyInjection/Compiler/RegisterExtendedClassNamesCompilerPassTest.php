<?php

declare(strict_types=1);

namespace Tests\FrameworkBundle\Unit\DependencyInjection\Compiler;

use PHPUnit\Framework\TestCase;
use Shopsys\FrameworkBundle\DependencyInjection\Compiler\RegisterExtendedClassNamesCompilerPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Tests\FrameworkBundle\Unit\DependencyInjection\Compiler\Source\RegisterExtendedClassNamesCompilerPassTest\Framework\DummyFrameworkImplementation;
use Tests\FrameworkBundle\Unit\DependencyInjection\Compiler\Source\RegisterExtendedClassNamesCompilerPassTest\Framework\DummyService;
use Tests\FrameworkBundle\Unit\DependencyInjection\Compiler\Source\RegisterExtendedClassNamesCompilerPassTest\Framework\DummyServiceInterface;
use Tests\FrameworkBundle\Unit\DependencyInjection\Compiler\Source\RegisterExtendedClassNamesCompilerPassTest\Framework\DummyUnrelatedService;
use Tests\FrameworkBundle\Unit\DependencyInjection\Compiler\Source\RegisterExtendedClassNamesCompilerPassTest\Package\src\Sub\DummyPackageClass;
use Tests\FrameworkBundle\Unit\DependencyInjection\Compiler\Source\RegisterExtendedClassNamesCompilerPassTest\Project\DummyExtendedService;
use Tests\FrameworkBundle\Unit\DependencyInjection\Compiler\Source\RegisterExtendedClassNamesCompilerPassTest\Project\Sub\DummyPackageClass as ProjectDummyPackageClass;

class RegisterExtendedClassNamesCompilerPassTest extends TestCase
{
    private const string FRAMEWORK_NAMESPACE_PREFIX = 'Tests\\FrameworkBundle\\Unit\\DependencyInjection\\Compiler\\Source\\RegisterExtendedClassNamesCompilerPassTest\\Framework\\';

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
            [
                DummyService::class => DummyExtendedService::class,
                DummyServiceInterface::class => DummyExtendedService::class,
            ],
            $container->getParameter(RegisterExtendedClassNamesCompilerPass::EXTENDED_CLASS_NAMES_PARAMETER),
        );
    }

    public function testDefinitionWithExtendingClassIsMapped(): void
    {
        $container = $this->createContainer();
        $container->register(DummyService::class, DummyExtendedService::class)->setPublic(true);

        $container->compile();

        $this->assertSame(
            [
                DummyService::class => DummyExtendedService::class,
                DummyServiceInterface::class => DummyExtendedService::class,
            ],
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

    public function testInterfaceAliasedToProjectImplementationIsMappedTogetherWithItsClass(): void
    {
        $container = $this->createContainer();
        $container->register(DummyExtendedService::class, DummyExtendedService::class)->setPublic(true);
        $container->setAlias(DummyServiceInterface::class, DummyExtendedService::class);

        $container->compile();

        $this->assertSame(
            [
                DummyServiceInterface::class => DummyExtendedService::class,
                DummyService::class => DummyExtendedService::class,
            ],
            $container->getParameter(RegisterExtendedClassNamesCompilerPass::EXTENDED_CLASS_NAMES_PARAMETER),
        );
    }

    public function testInterfaceAliasedToFrameworkImplementationIsNotMapped(): void
    {
        $container = $this->createContainer();
        $container->register(DummyFrameworkImplementation::class, DummyFrameworkImplementation::class)->setPublic(true);
        $container->setAlias(DummyServiceInterface::class, DummyFrameworkImplementation::class);

        $container->compile();

        $this->assertSame([], $container->getParameter(RegisterExtendedClassNamesCompilerPass::EXTENDED_CLASS_NAMES_PARAMETER));
    }

    public function testProjectClassExtendingPackageClassByNamingConventionIsMapped(): void
    {
        $container = $this->createContainer();
        $container->setParameter(RegisterExtendedClassNamesCompilerPass::PACKAGES_REGISTRY_PARAMETER, [
            'dummy' => [
                'path' => __DIR__ . '/Source/RegisterExtendedClassNamesCompilerPassTest/Package',
                'namespace' => 'Tests\\FrameworkBundle\\Unit\\DependencyInjection\\Compiler\\Source\\RegisterExtendedClassNamesCompilerPassTest\\Package\\src',
                'app_namespace' => 'Tests\\FrameworkBundle\\Unit\\DependencyInjection\\Compiler\\Source\\RegisterExtendedClassNamesCompilerPassTest\\Project',
            ],
        ]);

        $container->compile();

        $this->assertSame(
            [DummyPackageClass::class => ProjectDummyPackageClass::class],
            $container->getParameter(RegisterExtendedClassNamesCompilerPass::EXTENDED_CLASS_NAMES_PARAMETER),
        );
    }

    private function createContainer(): ContainerBuilder
    {
        $container = new ContainerBuilder();
        $container->addCompilerPass(new RegisterExtendedClassNamesCompilerPass(self::FRAMEWORK_NAMESPACE_PREFIX));

        return $container;
    }
}
