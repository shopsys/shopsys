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
            ->end();

        return $treeBuilder;
    }
}
