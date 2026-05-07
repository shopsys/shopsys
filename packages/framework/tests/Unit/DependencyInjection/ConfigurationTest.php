<?php

declare(strict_types=1);

namespace Tests\FrameworkBundle\Unit\DependencyInjection;

use PHPUnit\Framework\TestCase;
use Shopsys\FrameworkBundle\Component\PostDeploy\Task\PostDeployTaskRunEnum;
use Shopsys\FrameworkBundle\Component\PostDeploy\Task\RecalculateFileSizesTask;
use Shopsys\FrameworkBundle\DependencyInjection\Configuration;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Symfony\Component\Config\Definition\Processor;

class ConfigurationTest extends TestCase
{
    public function testPostDeployTaskCanBeOverriddenByProjectConfiguration(): void
    {
        $config = $this->processConfiguration([
            [
                'post_deploy' => [
                    'tasks' => [
                        'recalculate_file_sizes' => [
                            'run' => PostDeployTaskRunEnum::ALWAYS,
                            'priority' => 100,
                            'service' => RecalculateFileSizesTask::class,
                        ],
                    ],
                ],
            ],
            [
                'post_deploy' => [
                    'tasks' => [
                        'recalculate_file_sizes' => [
                            'run' => PostDeployTaskRunEnum::NEVER,
                        ],
                    ],
                ],
            ],
        ]);

        $this->assertSame(
            [
                'run' => PostDeployTaskRunEnum::NEVER,
                'priority' => 100,
                'service' => RecalculateFileSizesTask::class,
            ],
            $config['post_deploy']['tasks']['recalculate_file_sizes'],
        );
    }

    public function testPostDeployTaskRequiresServiceWhenItRuns(): void
    {
        $this->expectException(InvalidConfigurationException::class);
        $this->expectExceptionMessage('"service" is required');

        $this->processConfiguration([
            [
                'post_deploy' => [
                    'tasks' => [
                        'missing_service' => [
                            'run' => PostDeployTaskRunEnum::ALWAYS,
                        ],
                    ],
                ],
            ],
        ]);
    }

    public function testPostDeployTaskCanBeNeverWithoutService(): void
    {
        $config = $this->processConfiguration([
            [
                'post_deploy' => [
                    'tasks' => [
                        'disabled_task' => [
                            'run' => PostDeployTaskRunEnum::NEVER,
                        ],
                    ],
                ],
            ],
        ]);

        $this->assertSame(
            [
                'run' => PostDeployTaskRunEnum::NEVER,
                'priority' => 0,
                'service' => null,
            ],
            $config['post_deploy']['tasks']['disabled_task'],
        );
    }

    public function testPostDeployTaskNameMustBeSnakeCase(): void
    {
        $this->expectException(InvalidConfigurationException::class);
        $this->expectExceptionMessage('snake_case');

        $this->processConfiguration([
            [
                'post_deploy' => [
                    'tasks' => [
                        'BadName' => [
                            'run' => PostDeployTaskRunEnum::NEVER,
                        ],
                    ],
                ],
            ],
        ]);
    }

    /**
     * @param array<int, array<string, mixed>> $configs
     * @return array<string, mixed>
     */
    private function processConfiguration(array $configs): array
    {
        return (new Processor())->processConfiguration(new Configuration(), $configs);
    }
}
