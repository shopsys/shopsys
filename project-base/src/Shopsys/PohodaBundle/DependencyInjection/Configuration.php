<?php

namespace Shopsys\PohodaBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritDoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('shopsys_pohoda');

        $rootNode->children()
            ->arrayNode('jshopsys')
                ->children()
                    ->scalarNode('web_dir')->end()
                    ->scalarNode('verification_password')->end()
                    ->arrayNode('actions_routes')->ignoreExtraKeys(false)->end()
                ->end()
            ->end();

        return $treeBuilder;
    }
}
