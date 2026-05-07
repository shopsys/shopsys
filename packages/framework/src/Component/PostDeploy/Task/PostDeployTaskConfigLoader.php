<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\PostDeploy\Task;

use Psr\Container\ContainerInterface;
use Shopsys\FrameworkBundle\Component\PostDeploy\Task\Exception\PostDeployTaskConfigException;
use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\Filesystem\Exception\FileNotFoundException;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Yaml\Yaml;

class PostDeployTaskConfigLoader
{
    public function __construct(
        protected readonly Filesystem $filesystem,
        protected readonly ContainerInterface $taskServiceLocator,
        protected readonly PostDeployTaskRunEnum $postDeployTaskRunEnum,
    ) {
    }

    /**
     * @param string[] $filenames
     */
    public function loadFromYamlFiles(array $filenames): PostDeployTaskConfig
    {
        $entriesByName = [];
        $orderByName = [];
        $orderCounter = 0;
        $processor = new Processor();
        $definition = new PostDeployTaskConfigDefinition($this->postDeployTaskRunEnum);

        foreach ($filenames as $filename) {
            $rawConfig = $this->parseFile($filename);
            $processedConfig = $processor->processConfiguration($definition, [$rawConfig]);

            foreach ($processedConfig as $name => $entry) {
                $entriesByName[$name] = ['entry' => $entry, 'filename' => $filename];
                $orderByName[$name] = $orderCounter++;
            }
        }

        $descriptors = [];

        foreach ($entriesByName as $name => $data) {
            $descriptors[] = $this->buildDescriptor($name, $data['entry'], $data['filename']);
        }

        usort(
            $descriptors,
            static function (PostDeployTaskDescriptor $a, PostDeployTaskDescriptor $b) use ($orderByName): int {
                $priorityComparison = $b->priority <=> $a->priority;

                if ($priorityComparison !== 0) {
                    return $priorityComparison;
                }

                return $orderByName[$a->name] <=> $orderByName[$b->name];
            },
        );

        return new PostDeployTaskConfig($descriptors);
    }

    /**
     * @return array<string, mixed>
     */
    protected function parseFile(string $filename): array
    {
        if (!$this->filesystem->exists($filename)) {
            throw new FileNotFoundException(sprintf('File "%s" does not exist.', $filename));
        }

        $parsed = Yaml::parseFile($filename) ?? [];

        if (!is_array($parsed)) {
            throw new PostDeployTaskConfigException(sprintf(
                'Post-deploy tasks file "%s" must contain a YAML mapping at the root keyed by task name; got %s.',
                $filename,
                get_debug_type($parsed),
            ));
        }

        return $parsed;
    }

    /**
     * @param array<string, mixed> $entry
     */
    protected function buildDescriptor(string $name, array $entry, string $filename): PostDeployTaskDescriptor
    {
        $run = $entry[PostDeployTaskConfigDefinition::CONFIG_RUN];
        $priority = $entry[PostDeployTaskConfigDefinition::CONFIG_PRIORITY];

        if ($run === PostDeployTaskRunEnum::NEVER) {
            return new PostDeployTaskDescriptor(
                name: $name,
                run: $run,
                priority: $priority,
                task: null,
            );
        }

        return new PostDeployTaskDescriptor(
            name: $name,
            run: $run,
            priority: $priority,
            task: $this->resolveTask($entry[PostDeployTaskConfigDefinition::CONFIG_SERVICE], $name, $filename),
        );
    }

    protected function resolveTask(string $serviceFqcn, string $name, string $filename): PostDeployTaskInterface
    {
        if (!$this->taskServiceLocator->has($serviceFqcn)) {
            throw new PostDeployTaskConfigException(sprintf(
                'Service "%s" referenced by task "%s" in "%s" is not registered as a post-deploy task. Make sure the class implements %s and is registered in the service container.',
                $serviceFqcn,
                $name,
                $filename,
                PostDeployTaskInterface::class,
            ));
        }

        return $this->taskServiceLocator->get($serviceFqcn);
    }
}
