<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\DependencyInjection;

use Override;
use Shopsys\FrameworkBundle\Component\PostDeploy\Task\PostDeployTaskRunEnum;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;

class Configuration implements ConfigurationInterface
{
    protected const string POST_DEPLOY_TASK_NAME_PATTERN = '/^[a-z][a-z0-9_]*$/';

    #[Override]
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('shopsys_framework');
        $rootNode = $treeBuilder->getRootNode();

        $this->addOrderSection($rootNode);
        $this->addProductSection($rootNode);
        $this->addAdminContextSection($rootNode);
        $this->addPostDeploySection($rootNode);

        return $treeBuilder;
    }

    protected function addOrderSection(ArrayNodeDefinition $rootNode): void
    {
        $rootNode
            ->children()
                ->arrayNode('order')
                    ->children()
                        ->arrayNode('processing_middlewares')
                            ->scalarPrototype()->end()
                        ->end()
                    ->end()
                ->end()
            ->end();
    }

    protected function addProductSection(ArrayNodeDefinition $rootNode): void
    {
        $rootNode
            ->children()
                ->arrayNode('product')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->arrayNode('reviews')
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->arrayNode('enabled_domain_ids')
                                    ->integerPrototype()->end()
                                    ->defaultValue([])
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end();
    }

    protected function addAdminContextSection(ArrayNodeDefinition $rootNode): void
    {
        $rootNode
            ->children()
                ->arrayNode('admin_context_additional_path_prefixes')
                    ->scalarPrototype()->end()
                ->end()
            ->end();
    }

    protected function addPostDeploySection(ArrayNodeDefinition $rootNode): void
    {
        $rootNode
            ->children()
                ->arrayNode('post_deploy')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->arrayNode('tasks')
                            ->normalizeKeys(false)
                            ->useAttributeAsKey('name')
                            ->arrayPrototype()
                                ->children()
                                    ->enumNode('run')
                                        ->isRequired()
                                        ->values((new PostDeployTaskRunEnum())->getAllCases())
                                    ->end()
                                    ->integerNode('priority')
                                        ->defaultValue(0)
                                    ->end()
                                    ->scalarNode('service')
                                        ->cannotBeEmpty()
                                        ->defaultNull()
                                    ->end()
                                ->end()
                                ->validate()
                                    ->ifTrue(static fn (array $entry): bool => $entry['run'] !== PostDeployTaskRunEnum::NEVER && $entry['service'] === null)
                                    ->thenInvalid('Field "service" is required when "run" is "one_time" or "always".')
                                ->end()
                            ->end()
                            ->validate()
                                ->always(static function (array $tasks): array {
                                    foreach (array_keys($tasks) as $name) {
                                        if (preg_match(self::POST_DEPLOY_TASK_NAME_PATTERN, (string)$name) !== 1) {
                                            throw new InvalidConfigurationException(sprintf(
                                                'Post-deploy task key must be a non-empty snake_case string (lowercase letters, digits, underscores; starting with a letter); got "%s".',
                                                (string)$name,
                                            ));
                                        }
                                    }

                                    return $tasks;
                                })
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end();
    }
}
