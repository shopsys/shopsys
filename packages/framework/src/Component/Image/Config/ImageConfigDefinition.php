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
    public const CONFIG_SIZES = 'sizes';
    public const CONFIG_SIZE_NAME = 'name';
    public const CONFIG_SIZE_WIDTH = 'width';
    public const CONFIG_SIZE_HEIGHT = 'height';
    public const CONFIG_SIZE_CROP = 'crop';
    public const CONFIG_SIZE_OCCURRENCE = 'occurrence';
    public const CONFIG_SIZE_ADDITIONAL_SIZES = 'additionalSizes';
    public const CONFIG_SIZE_ADDITIONAL_SIZE_MEDIA = 'media';
    protected const CONFIG_IMAGES = 'images';

    /**
     * @return \Symfony\Component\Config\Definition\Builder\TreeBuilder
     */
    public function getConfigTreeBuilder(): \Symfony\Component\Config\Definition\Builder\TreeBuilder
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
    protected function buildItemsNode(ArrayNodeDefinition $node): \Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition
    {
        return $node
            ->addDefaultsIfNotSet()
            ->children()
                ->scalarNode(self::CONFIG_ENTITY_NAME)->isRequired()->cannotBeEmpty()->end()
                ->scalarNode(self::CONFIG_CLASS)->isRequired()->cannotBeEmpty()->end()
                ->scalarNode(self::CONFIG_MULTIPLE)->defaultFalse()->end()
                ->append($this->createSizesNode())
                ->arrayNode(self::CONFIG_TYPES)
                    ->defaultValue([])
                    ->arrayPrototype()
                        ->children()
                            ->scalarNode(self::CONFIG_TYPE_NAME)->isRequired()->cannotBeEmpty()->end()
                            ->scalarNode(self::CONFIG_MULTIPLE)->defaultFalse()->end()
                            ->append($this->createSizesNode())
                        ->end()
                    ->end()
                ->end()
            ->end();
    }

    /**
     * @return \Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition
     */
    protected function createSizesNode(): \Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition
    {
        $treeBuilder = new TreeBuilder(self::CONFIG_SIZES);
        /** @var \Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition $rootNode */
        $rootNode = $treeBuilder->getRootNode();

        return $rootNode
            ->defaultValue([])
            ->arrayPrototype()
                ->children()
                    ->scalarNode(self::CONFIG_SIZE_NAME)->isRequired()->end()
                    ->scalarNode(self::CONFIG_SIZE_WIDTH)->defaultNull()->end()
                    ->scalarNode(self::CONFIG_SIZE_HEIGHT)->defaultNull()->end()
                    ->scalarNode(self::CONFIG_SIZE_CROP)->defaultFalse()->end()
                    ->scalarNode(self::CONFIG_SIZE_OCCURRENCE)->defaultNull()->end()
                    ->arrayNode(self::CONFIG_SIZE_ADDITIONAL_SIZES)
                        ->defaultValue([])
                        ->arrayPrototype()
                            ->children()
                                ->scalarNode(self::CONFIG_SIZE_ADDITIONAL_SIZE_MEDIA)->isRequired()->end()
                                ->scalarNode(self::CONFIG_SIZE_WIDTH)->defaultNull()->end()
                                ->scalarNode(self::CONFIG_SIZE_HEIGHT)->defaultNull()->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end();
    }
}
