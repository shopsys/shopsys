<?php

namespace Shopsys\FrameworkBundle\Component\Domain\Config;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class DomainsUrlsConfigDefinition implements ConfigurationInterface
{
    public const CONFIG_DOMAINS_URLS = 'domains_urls';
    public const CONFIG_ID = 'id';
    public const CONFIG_URL = 'url';

    /**
     * @return \Symfony\Component\Config\Definition\Builder\TreeBuilder
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder(self::CONFIG_DOMAINS_URLS);
        $rootNode = $treeBuilder->getRootNode();

        $rootNode
            ->children()
                ->arrayNode(self::CONFIG_DOMAINS_URLS)
                ->useAttributeAsKey(self::CONFIG_ID, false)
                    ->prototype('array')
                        ->children()
                            ->scalarNode(self::CONFIG_ID)->isRequired()->cannotBeEmpty()->end()
                            ->scalarNode(self::CONFIG_URL)->isRequired()->cannotBeEmpty()->end()
                        ->end()
                    ->end()
                ->end()
            ->end();

        return $treeBuilder;
    }
}
