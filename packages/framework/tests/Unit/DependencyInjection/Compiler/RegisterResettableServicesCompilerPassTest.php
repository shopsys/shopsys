<?php

declare(strict_types=1);

namespace Tests\FrameworkBundle\Unit\DependencyInjection\Compiler;

use PHPUnit\Framework\TestCase;
use Shopsys\FrameworkBundle\DependencyInjection\Compiler\RegisterResettableServicesCompilerPass;
use Shopsys\FrameworkBundle\DependencyInjection\ServicesResetter;
use Symfony\Component\DependencyInjection\Argument\AbstractArgument;
use Symfony\Component\DependencyInjection\Argument\IteratorArgument;
use Symfony\Component\DependencyInjection\ChildDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\Dumper\PhpDumper;
use Symfony\Component\DependencyInjection\Reference;
use Tests\FrameworkBundle\Unit\DependencyInjection\Compiler\Source\RegisterResettableServicesCompilerPassTest\DummyResettableService;

class RegisterResettableServicesCompilerPassTest extends TestCase
{
    public function testContainerWithUnusedResettableTemplateOfChildDefinitionCanBeDumped(): void
    {
        $container = $this->createContainer();
        $container->register('template', DummyResettableService::class)
            ->setArguments([new AbstractArgument('set in child definitions')])
            ->addTag('kernel.reset', ['method' => 'reset']);
        $container->setDefinition('child', new ChildDefinition('template'))
            ->replaceArgument(0, 'child')
            ->addTag('kernel.reset', ['method' => 'reset'])
            ->setPublic(true);

        $container->compile();

        $this->assertFalse($container->hasDefinition('template'));
        $this->assertTrue($container->hasDefinition('child'));
        $this->assertIsString((new PhpDumper($container))->dump());
    }

    public function testResettableServicesAreReferencedOnlyWhenInitialized(): void
    {
        $container = $this->createContainer();
        $container->register('resettable', DummyResettableService::class)
            ->addTag('kernel.reset', ['method' => 'reset']);
        $container->register('not_resettable', DummyResettableService::class);

        (new RegisterResettableServicesCompilerPass())->process($container);

        $resettableServices = $container->getDefinition(ServicesResetter::class)->getArgument('$resettableServices');

        $this->assertInstanceOf(IteratorArgument::class, $resettableServices);
        $this->assertEquals(
            ['resettable' => new Reference('resettable', ContainerInterface::IGNORE_ON_UNINITIALIZED_REFERENCE)],
            $resettableServices->getValues(),
        );
    }

    private function createContainer(): ContainerBuilder
    {
        $container = new ContainerBuilder();
        $container->addCompilerPass(new RegisterResettableServicesCompilerPass());
        $container->register(ServicesResetter::class, ServicesResetter::class)->setPublic(true);

        return $container;
    }
}
