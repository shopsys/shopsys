<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\UploadedFile\Config;

use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class UploadedFileConfigDefinition implements ConfigurationInterface
{
    public const CONFIG_CLASS = 'class';
    public const CONFIG_ENTITY_NAME = 'name';
    public const CONFIG_TYPES = 'types';
    public const CONFIG_TYPE_NAME = 'name';
    public const CONFIG_TYPE_MULTIPLE = 'multiple';
    protected const CONFIG_ENTITY_FILES = 'entity_files';

    /**
     * @return \Symfony\Component\Config\Definition\Builder\TreeBuilder
     */
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder(static::CONFIG_ENTITY_FILES);
        /** @var \Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition $rootNode */
        $rootNode = $treeBuilder->getRootNode();

        $this->buildItemsNode($rootNode->arrayPrototype())->end();

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
                ->arrayNode(self::CONFIG_TYPES)
                    ->defaultValue([])
                    ->arrayPrototype()
                        ->children()
                            ->scalarNode(self::CONFIG_TYPE_NAME)->isRequired()->cannotBeEmpty()->end()
                            ->scalarNode(self::CONFIG_TYPE_MULTIPLE)->defaultFalse()->end()
                        ->end()
                    ->end()
                ->end()
            ->end();
    }
}
