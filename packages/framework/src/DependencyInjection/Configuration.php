<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\DependencyInjection;

use Override;
use Shopsys\FrameworkBundle\Component\PostDeploy\Task\PostDeployTaskRunEnum;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    protected const string POST_DEPLOY_TASK_NAME_PATTERN = '/^[a-z][a-z0-9_]*$/';

    #[Override]
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('shopsys_framework');

        $treeBuilder->getRootNode()
            ->children()
                ->arrayNode('order')
                    ->children()
                        ->arrayNode('processing_middlewares')
                            ->scalarPrototype()->end()
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('admin_context_additional_path_prefixes')
                    ->scalarPrototype()->end()
                ->end()
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
                                        ->values([
                                            PostDeployTaskRunEnum::ALWAYS,
                                            PostDeployTaskRunEnum::ONE_TIME,
                                            PostDeployTaskRunEnum::NEVER,
                                        ])
                                    ->end()
                                    ->integerNode('priority')
                                        ->defaultValue(0)
                                    ->end()
                                    ->scalarNode('service')
                                        ->cannotBeEmpty()
                                        ->defaultNull()
                                        ->validate()
                                            ->ifTrue(static fn ($value): bool => $value !== null && !is_string($value))
                                            ->thenInvalid('Field "service" must be a string service ID.')
                                        ->end()
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
                                        if (!is_string($name) || preg_match(self::POST_DEPLOY_TASK_NAME_PATTERN, $name) !== 1) {
                                            throw new InvalidConfigurationException(sprintf(
                                                'Post-deploy task key must be a non-empty snake_case string (lowercase letters, digits, underscores; starting with a letter); got "%s".',
                                                is_string($name) ? $name : get_debug_type($name),
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

        return $treeBuilder;
    }
}
