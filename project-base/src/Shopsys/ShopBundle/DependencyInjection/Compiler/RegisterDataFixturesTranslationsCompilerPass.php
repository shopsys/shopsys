<?php

namespace Shopsys\ShopBundle\DependencyInjection\Compiler;

use Shopsys\ShopBundle\DataFixtures\Translations\DataFixturesTranslations;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class RegisterDataFixturesTranslationsCompilerPass implements CompilerPassInterface
{
    /**
     * @param \Symfony\Component\DependencyInjection\ContainerBuilder $container
     */
    public function process(ContainerBuilder $container)
    {
        $dataFixturesTranslationsDefinition = $container->findDefinition(DataFixturesTranslations::class);

        $taggedServiceIds = $container->findTaggedServiceIds('shopsys.datafixtures.translations');
        foreach ($taggedServiceIds as $serviceId => $tags) {
            $dataFixturesTranslationsDefinition->addMethodCall(
                'registerTranslation',
                [
                    new Reference($serviceId),
                    $serviceId,
                ]
            );
        }
    }
}
