<?php

declare(strict_types=1);

namespace Shopsys\McpBundle\DependencyInjection;

use Override;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    #[Override]
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('shopsys_mcp');

        $treeBuilder->getRootNode()
            ->children()
                ->arrayNode('authorization')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->integerNode('access_token_ttl_seconds')
                            ->min(1)
                            ->defaultValue(2592000)
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('query')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->integerNode('max_returned_rows')
                            ->min(1)
                            ->defaultValue(500)
                        ->end()
                        ->integerNode('statement_timeout_ms')
                            ->min(1)
                            ->defaultValue(10000)
                        ->end()
                        ->integerNode('lock_timeout_ms')
                            ->min(1)
                            ->defaultValue(1000)
                        ->end()
                    ->end()
                ->end()
            ->end();

        return $treeBuilder;
    }
}
