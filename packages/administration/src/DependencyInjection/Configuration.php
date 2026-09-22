<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\DependencyInjection;

use Override;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public const string EXTENSION_ALIAS = 'shopsys_administration';

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder(self::EXTENSION_ALIAS);
        $rootNode = $treeBuilder->getRootNode();

        $rootNode
            ->children()
                ->arrayNode('access_control')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->arrayNode('additional_excluded_route_names')
                            ->info('Additional route names to exclude from access control checks (extends the default list)')
                            ->defaultValue([])
                            ->scalarPrototype()->end()
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('datagrid')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->integerNode('max_domains_rendered_as_tabs')
                            ->info('How many domains the domain filter of a datagrid offers as a row of tabs; above this many it offers a dropdown instead')
                            ->min(1)
                            ->defaultValue(6)
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('roles')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->booleanNode('simple_permissions')
                            ->info('Whether to use simple permissions (VIEW, FULL) instead of all permissions')
                            ->defaultValue(false)
                        ->end()
                    ->end()
                ->end()
            ->end();

        return $treeBuilder;
    }
}
