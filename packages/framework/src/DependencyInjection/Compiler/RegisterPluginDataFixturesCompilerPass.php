<?php

namespace Shopsys\FrameworkBundle\DependencyInjection\Compiler;

use Shopsys\FrameworkBundle\Component\Plugin\PluginDataFixtureRegistry;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

class RegisterPluginDataFixturesCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        $pluginDataFixtureRegistryDefinition = $container->findDefinition(
            PluginDataFixtureRegistry::class
        );
        $taggedServiceIds = $container->findTaggedServiceIds('shopsys.data_fixture');
        foreach (array_keys($taggedServiceIds) as $serviceId) {
            $this->registerDataFixture($pluginDataFixtureRegistryDefinition, $serviceId);
        }
    }

    private function registerDataFixture(Definition $pluginDataFixtureRegistryDefinition, string $serviceId): void
    {
        $pluginDataFixtureRegistryDefinition->addMethodCall('registerDataFixture', [new Reference($serviceId)]);
    }
}
