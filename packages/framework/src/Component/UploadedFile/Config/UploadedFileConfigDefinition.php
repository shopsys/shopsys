<?php

namespace Shopsys\FrameworkBundle\Component\UploadedFile\Config;

use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class UploadedFileConfigDefinition implements ConfigurationInterface
{
    const CONFIG_CLASS = 'class';
    const CONFIG_ENTITY_NAME = 'name';

    public function getConfigTreeBuilder(): \Symfony\Component\Config\Definition\Builder\TreeBuilder
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('entity_files');

        $this->buildItemsNode($rootNode->prototype('array'))->end();

        return $treeBuilder;
    }

    private function buildItemsNode(ArrayNodeDefinition $node): \Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition
    {
        return $node
            ->addDefaultsIfNotSet()
            ->children()
                ->scalarNode(self::CONFIG_ENTITY_NAME)->isRequired()->cannotBeEmpty()->end()
                ->scalarNode(self::CONFIG_CLASS)->isRequired()->cannotBeEmpty()->end()
                ->end()
            ->end();
    }
}
