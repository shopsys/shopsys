<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\CustomerUploadedFile\Config;

use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class CustomerUploadedFileConfigDefinition implements ConfigurationInterface
{
    public const string CONFIG_CLASS = 'class';
    public const string CONFIG_ENTITY_NAME = 'name';
    public const string CONFIG_TYPES = 'types';
    public const string CONFIG_TYPE_NAME = 'name';
    public const string CONFIG_TYPE_MULTIPLE = 'multiple';
    protected const string CONFIG_ENTITY_FILES = 'entity_files';

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
    protected function buildItemsNode(ArrayNodeDefinition $node): ArrayNodeDefinition
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
