<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Image\Config;

use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class ImageConfigDefinition implements ConfigurationInterface
{
    public const CONFIG_CLASS = 'class';
    public const CONFIG_ENTITY_NAME = 'name';
    public const CONFIG_MULTIPLE = 'multiple';
    public const CONFIG_TYPES = 'types';
    public const CONFIG_TYPE_NAME = 'name';
    protected const CONFIG_IMAGES = 'images';

    /**
     * @return \Symfony\Component\Config\Definition\Builder\TreeBuilder
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder(static::CONFIG_IMAGES);
        /** @var \Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition $rootNode */
        $rootNode = $treeBuilder->getRootNode();

        $this->buildItemsNode($rootNode->arrayPrototype());

        return $treeBuilder;
    }

    /**
     * @param \Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition $node
     * @return \Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition
     */
    protected function buildItemsNode(ArrayNodeDefinition $node)
    {
        return $node
            ->addDefaultsIfNotSet()
            ->children()
                ->scalarNode(self::CONFIG_ENTITY_NAME)->isRequired()->cannotBeEmpty()->end()
                ->scalarNode(self::CONFIG_CLASS)->isRequired()->cannotBeEmpty()->end()
                ->scalarNode(self::CONFIG_MULTIPLE)->defaultFalse()->end()
                ->arrayNode(self::CONFIG_TYPES)
                    ->defaultValue([])
                    ->arrayPrototype()
                        ->children()
                            ->scalarNode(self::CONFIG_TYPE_NAME)->isRequired()->cannotBeEmpty()->end()
                            ->scalarNode(self::CONFIG_MULTIPLE)->defaultFalse()->end()
                        ->end()
                    ->end()
                ->end()
            ->end();
    }
}
