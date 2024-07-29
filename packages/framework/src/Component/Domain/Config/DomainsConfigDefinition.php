<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Domain\Config;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class DomainsConfigDefinition implements ConfigurationInterface
{
    public const string CONFIG_DOMAINS = 'domains';
    public const string CONFIG_ID = 'id';
    public const string CONFIG_NAME = 'name';
    public const string CONFIG_LOCALE = 'locale';
    public const string CONFIG_TIMEZONE = 'timezone';
    public const string CONFIG_STYLES_DIRECTORY = 'styles_directory';
    public const string CONFIG_DESIGN_ID = 'design_id';
    public const string CONFIG_TYPE = 'type';
    public const string CONFIG_LOAD_DEMO_DATA = 'load_demo_data';

    /**
     * @return \Symfony\Component\Config\Definition\Builder\TreeBuilder
     */
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder(self::CONFIG_DOMAINS);
        /** @var \Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition $rootNode */
        $rootNode = $treeBuilder->getRootNode();

        $rootNode
            ->children()
                ->arrayNode(self::CONFIG_DOMAINS)
                    ->useAttributeAsKey(self::CONFIG_ID, false)
                    ->prototype('array')
                        ->children()
                            ->scalarNode(self::CONFIG_ID)->isRequired()->cannotBeEmpty()->end()
                            ->scalarNode(self::CONFIG_NAME)->isRequired()->cannotBeEmpty()->end()
                            ->scalarNode(self::CONFIG_LOCALE)->isRequired()->cannotBeEmpty()->end()
                            ->scalarNode(self::CONFIG_TIMEZONE)->isRequired()->cannotBeEmpty()->end()
                            ->scalarNode(self::CONFIG_STYLES_DIRECTORY)->defaultValue(
                                DomainConfig::STYLES_DIRECTORY_DEFAULT,
                            )->end()
                            ->scalarNode(self::CONFIG_DESIGN_ID)->defaultNull()->end()
                            ->scalarNode(self::CONFIG_TYPE)->defaultValue(
                                DomainConfig::TYPE_B2C,
                            )->end()
                            ->booleanNode(self::CONFIG_LOAD_DEMO_DATA)->defaultTrue()
                        ->end()
                    ->end()
                ->end()
            ->end();

        return $treeBuilder;
    }
}
