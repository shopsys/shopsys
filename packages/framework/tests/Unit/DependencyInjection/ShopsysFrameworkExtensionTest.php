<?php

declare(strict_types=1);

namespace Tests\FrameworkBundle\Unit\DependencyInjection;

use PHPUnit\Framework\TestCase;
use Shopsys\FrameworkBundle\Component\PostDeploy\Task\PostDeployTaskConfig;
use Shopsys\FrameworkBundle\Component\PostDeploy\Task\PostDeployTaskDescriptor;
use Shopsys\FrameworkBundle\Component\PostDeploy\Task\PostDeployTaskRunEnum;
use Shopsys\FrameworkBundle\Component\PostDeploy\Task\RecalculateFileSizesTask;
use Shopsys\FrameworkBundle\Component\PostDeploy\Task\SetCustomerUploadedFilesPrivateVisibilityTask;
use Shopsys\FrameworkBundle\DependencyInjection\Configuration;
use Shopsys\FrameworkBundle\DependencyInjection\ShopsysFrameworkExtension;
use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

class ShopsysFrameworkExtensionTest extends TestCase
{
    public function testPostDeployTaskDescriptorsAreConfiguredInPriorityOrder(): void
    {
        $container = new ContainerBuilder();
        $container->setDefinition(PostDeployTaskConfig::class, new Definition(PostDeployTaskConfig::class));
        $extension = new ShopsysFrameworkExtension();

        $extension->setPostDeployTasksConfig([
            'low_priority' => [
                'run' => PostDeployTaskRunEnum::ALWAYS,
                'priority' => 10,
                'service' => 'low_service',
            ],
            'disabled' => [
                'run' => PostDeployTaskRunEnum::NEVER,
                'priority' => 100,
                'service' => null,
            ],
            'same_priority' => [
                'run' => PostDeployTaskRunEnum::ONE_TIME,
                'priority' => 10,
                'service' => RecalculateFileSizesTask::class,
            ],
        ], $container);

        $descriptorDefinitions = $container
            ->getDefinition(PostDeployTaskConfig::class)
            ->getArgument('$descriptors');

        $this->assertCount(3, $descriptorDefinitions);
        $this->assertDescriptorDefinition($descriptorDefinitions[0], 'disabled', PostDeployTaskRunEnum::NEVER, 100, null);
        $this->assertDescriptorDefinition($descriptorDefinitions[1], 'low_priority', PostDeployTaskRunEnum::ALWAYS, 10, 'low_service');
        $this->assertDescriptorDefinition($descriptorDefinitions[2], 'same_priority', PostDeployTaskRunEnum::ONE_TIME, 10, RecalculateFileSizesTask::class);
    }

    public function testFrameworkPrependedTaskComesBeforeProjectTaskWithSamePriority(): void
    {
        $container = new ContainerBuilder();
        $container->setDefinition(PostDeployTaskConfig::class, new Definition(PostDeployTaskConfig::class));
        $extension = new ShopsysFrameworkExtension();
        $container->registerExtension($extension);

        $extension->prepend($container);
        $container->loadFromExtension('shopsys_framework', [
            'post_deploy' => [
                'tasks' => [
                    'project_task' => [
                        'run' => PostDeployTaskRunEnum::ALWAYS,
                        'priority' => 100,
                        'service' => 'project_service',
                    ],
                ],
            ],
        ]);

        $processedConfig = (new Processor())->processConfiguration(
            new Configuration(),
            $container->getExtensionConfig('shopsys_framework'),
        );

        $extension->setPostDeployTasksConfig($processedConfig['post_deploy']['tasks'], $container);

        $descriptorDefinitions = $container
            ->getDefinition(PostDeployTaskConfig::class)
            ->getArgument('$descriptors');

        $this->assertCount(3, $descriptorDefinitions);
        $this->assertDescriptorDefinition($descriptorDefinitions[0], 'recalculate_file_sizes', PostDeployTaskRunEnum::ONE_TIME, 100, RecalculateFileSizesTask::class);
        $this->assertDescriptorDefinition($descriptorDefinitions[1], 'project_task', PostDeployTaskRunEnum::ALWAYS, 100, 'project_service');
        $this->assertDescriptorDefinition(
            $descriptorDefinitions[2],
            'set_customer_uploaded_files_private_visibility',
            PostDeployTaskRunEnum::ONE_TIME,
            90,
            SetCustomerUploadedFilesPrivateVisibilityTask::class,
        );
    }

    private function assertDescriptorDefinition(
        Definition $descriptorDefinition,
        string $expectedName,
        string $expectedRun,
        int $expectedPriority,
        ?string $expectedServiceId,
    ): void {
        $this->assertSame(PostDeployTaskDescriptor::class, $descriptorDefinition->getClass());
        $this->assertSame($expectedName, $descriptorDefinition->getArgument('$name'));
        $this->assertSame($expectedRun, $descriptorDefinition->getArgument('$run'));
        $this->assertSame($expectedPriority, $descriptorDefinition->getArgument('$priority'));

        $taskArgument = $descriptorDefinition->getArgument('$task');

        if ($expectedServiceId === null) {
            $this->assertNull($taskArgument);

            return;
        }

        $this->assertInstanceOf(Reference::class, $taskArgument);
        $this->assertSame($expectedServiceId, (string)$taskArgument);
    }
}
