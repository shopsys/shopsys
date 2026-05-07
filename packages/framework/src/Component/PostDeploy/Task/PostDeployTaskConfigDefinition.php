<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\PostDeploy\Task;

use Override;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;

class PostDeployTaskConfigDefinition implements ConfigurationInterface
{
    public const string CONFIG_RUN = 'run';
    public const string CONFIG_SERVICE = 'service';
    public const string CONFIG_PRIORITY = 'priority';
    protected const string CONFIG_ROOT = 'post_deploy_tasks';
    protected const string NAME_PATTERN = '/^[a-z][a-z0-9_]*$/';

    public function __construct(
        protected readonly PostDeployTaskRunEnum $postDeployTaskRunEnum,
    ) {
    }

    #[Override]
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder(static::CONFIG_ROOT);
        $rootNode = $treeBuilder->getRootNode();

        $rootNode
            ->normalizeKeys(false)
            ->beforeNormalization()
                ->ifTrue(static fn ($value): bool => is_array($value) && $value !== [] && array_is_list($value))
                ->then(static function (): never {
                    throw new InvalidConfigurationException('Post-deploy tasks file must contain a YAML mapping at the root keyed by task name; a list was given.');
                })
            ->end()
            ->arrayPrototype()
                ->children()
                    ->enumNode(self::CONFIG_RUN)
                        ->isRequired()
                        ->values($this->postDeployTaskRunEnum->getAllCases())
                    ->end()
                    ->scalarNode(self::CONFIG_SERVICE)
                        ->cannotBeEmpty()
                        ->defaultNull()
                    ->end()
                    ->integerNode(self::CONFIG_PRIORITY)
                        ->defaultValue(0)
                    ->end()
                ->end()
                ->validate()
                    ->ifTrue(static fn (array $entry): bool => $entry[self::CONFIG_RUN] !== PostDeployTaskRunEnum::NEVER && $entry[self::CONFIG_SERVICE] === null)
                    ->thenInvalid('Field "service" is required when "run" is "one_time" or "always".')
                ->end()
            ->end()
            ->validate()
                ->always(static function (array $config): array {
                    foreach (array_keys($config) as $name) {
                        if (!is_string($name) || preg_match(self::NAME_PATTERN, $name) !== 1) {
                            throw new InvalidConfigurationException(sprintf(
                                'Task key must be a non-empty snake_case string (lowercase letters, digits, underscores; starting with a letter); got "%s".',
                                is_string($name) ? $name : get_debug_type($name),
                            ));
                        }
                    }

                    return $config;
                })
            ->end();

        return $treeBuilder;
    }
}
