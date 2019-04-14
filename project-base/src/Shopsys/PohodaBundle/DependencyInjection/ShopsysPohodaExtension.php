<?php

namespace Shopsys\PohodaBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\ConfigurableExtension;

class ShopsysPohodaExtension extends ConfigurableExtension
{
    /**
     * {@inheritDoc}
     */
    protected function loadInternal(array $config, ContainerBuilder $container)
    {
        $loader = new YamlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));

        $loader->load('services.yml');

        $this->injectAliasesForJShopsysActions($container, $config['jshopsys']['actions_routes']);
    }

    /**
     * @param \Symfony\Component\DependencyInjection\ContainerBuilder $container
     * @param array $jShopsysActionsRoutes
     * @param string $jShopsysActionServiceNamePrefix
     */
    private function injectAliasesForJShopsysActions(ContainerBuilder $container, array $jShopsysActionsRoutes, string $jShopsysActionServiceNamePrefix = '')
    {
        foreach ($jShopsysActionsRoutes as $jShopsysActionsRoutesGroup => $objectNameOrArray) {
            if (is_array($objectNameOrArray)) {
                $this->injectAliasesForJShopsysActions(
                    $container,
                    $objectNameOrArray,
                    $jShopsysActionServiceNamePrefix . $jShopsysActionsRoutesGroup . '.'
                );
            } else {
                $container->setAlias(
                    'shopsys_pohoda.jshopsys.actions_routes.' . $jShopsysActionServiceNamePrefix . $jShopsysActionsRoutesGroup,
                    $objectNameOrArray
                );
            }
        }
    }
}
