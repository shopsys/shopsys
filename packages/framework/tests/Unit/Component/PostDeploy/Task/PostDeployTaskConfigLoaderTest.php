<?php

declare(strict_types=1);

namespace Tests\FrameworkBundle\Unit\Component\PostDeploy\Task;

use Override;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\TestCase;
use Shopsys\FrameworkBundle\Component\PostDeploy\Task\Exception\PostDeployTaskConfigException;
use Shopsys\FrameworkBundle\Component\PostDeploy\Task\PostDeployTaskConfigLoader;
use Shopsys\FrameworkBundle\Component\PostDeploy\Task\PostDeployTaskInterface;
use Shopsys\FrameworkBundle\Component\PostDeploy\Task\PostDeployTaskRunEnum;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Symfony\Component\DependencyInjection\ServiceLocator;
use Symfony\Component\Filesystem\Exception\FileNotFoundException;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Yaml\Yaml;

#[AllowMockObjectsWithoutExpectations]
class PostDeployTaskConfigLoaderTest extends TestCase
{
    /**
     * @var string[]
     */
    private array $tempFiles = [];

    #[Override]
    protected function tearDown(): void
    {
        foreach ($this->tempFiles as $tempFile) {
            if (is_file($tempFile)) {
                unlink($tempFile);
            }
        }
        $this->tempFiles = [];

        parent::tearDown();
    }

    public function testParsesValidEntries(): void
    {
        $taskA = $this->createStubTask();
        $taskB = $this->createStubTask();

        $file = $this->writeYaml([
            'task_a' => ['run' => 'one_time', 'service' => $taskA::class, 'priority' => 100],
            'task_b' => ['run' => 'always', 'service' => $taskB::class],
        ]);

        $loader = $this->buildLoader([
            $taskA::class => $taskA,
            $taskB::class => $taskB,
        ]);

        $descriptors = $loader->loadFromYamlFiles([$file])->getDescriptors();

        $this->assertCount(2, $descriptors);
        $this->assertSame('task_a', $descriptors[0]->name);
        $this->assertSame(PostDeployTaskRunEnum::ONE_TIME, $descriptors[0]->run);
        $this->assertSame(100, $descriptors[0]->priority);
        $this->assertSame($taskA, $descriptors[0]->task);

        $this->assertSame('task_b', $descriptors[1]->name);
        $this->assertSame(PostDeployTaskRunEnum::ALWAYS, $descriptors[1]->run);
        $this->assertSame(0, $descriptors[1]->priority);
    }

    public function testMergesAcrossFilesAndPreservesPriorityOrder(): void
    {
        $taskHighFwk = $this->createStubTask();
        $taskLowFwk = $this->createStubTask();
        $taskHighApp = $this->createStubTask();
        $taskLowApp = $this->createStubTask();

        $frameworkFile = $this->writeYaml([
            'fwk_high' => ['run' => 'one_time', 'priority' => 100, 'service' => $taskHighFwk::class],
            'fwk_low' => ['run' => 'one_time', 'priority' => 10, 'service' => $taskLowFwk::class],
        ]);
        $appFile = $this->writeYaml([
            'app_high' => ['run' => 'one_time', 'priority' => 100, 'service' => $taskHighApp::class],
            'app_low' => ['run' => 'one_time', 'priority' => 10, 'service' => $taskLowApp::class],
        ]);

        $loader = $this->buildLoader([
            $taskHighFwk::class => $taskHighFwk,
            $taskLowFwk::class => $taskLowFwk,
            $taskHighApp::class => $taskHighApp,
            $taskLowApp::class => $taskLowApp,
        ]);

        $descriptors = $loader->loadFromYamlFiles([$frameworkFile, $appFile])->getDescriptors();
        $names = array_map(static fn ($d) => $d->name, $descriptors);

        $this->assertSame(['fwk_high', 'app_high', 'fwk_low', 'app_low'], $names);
    }

    public function testProjectFileOverridesFrameworkEntryByKey(): void
    {
        $frameworkTask = $this->createStubTask();
        $projectTask = $this->createStubTask();

        $frameworkFile = $this->writeYaml([
            'shared' => ['run' => 'one_time', 'priority' => 100, 'service' => $frameworkTask::class],
        ]);
        $appFile = $this->writeYaml([
            'shared' => ['run' => 'always', 'priority' => 5, 'service' => $projectTask::class],
        ]);

        $loader = $this->buildLoader([
            $frameworkTask::class => $frameworkTask,
            $projectTask::class => $projectTask,
        ]);

        $descriptors = $loader->loadFromYamlFiles([$frameworkFile, $appFile])->getDescriptors();

        $this->assertCount(1, $descriptors);
        $this->assertSame(PostDeployTaskRunEnum::ALWAYS, $descriptors[0]->run);
        $this->assertSame(5, $descriptors[0]->priority);
        $this->assertSame($projectTask, $descriptors[0]->task);
    }

    public function testRunNeverOverrideFromProjectFileOmitsService(): void
    {
        $frameworkTask = $this->createStubTask();

        $frameworkFile = $this->writeYaml([
            'shared' => ['run' => 'one_time', 'service' => $frameworkTask::class],
        ]);
        $appFile = $this->writeYaml([
            'shared' => ['run' => 'never'],
        ]);

        $loader = $this->buildLoader([$frameworkTask::class => $frameworkTask]);

        $descriptors = $loader->loadFromYamlFiles([$frameworkFile, $appFile])->getDescriptors();

        $this->assertCount(1, $descriptors);
        $this->assertSame(PostDeployTaskRunEnum::NEVER, $descriptors[0]->run);
        $this->assertNull($descriptors[0]->task);
    }

    public function testThrowsWhenRunIsMissing(): void
    {
        $task = $this->createStubTask();
        $file = $this->writeYaml([
            'no_run' => ['service' => $task::class],
        ]);

        $loader = $this->buildLoader([$task::class => $task]);

        $this->expectException(InvalidConfigurationException::class);
        $this->expectExceptionMessage('"run"');

        $loader->loadFromYamlFiles([$file]);
    }

    public function testThrowsOnInvalidRunValue(): void
    {
        $task = $this->createStubTask();
        $file = $this->writeYaml([
            'bad_run' => ['run' => 'sometimes', 'service' => $task::class],
        ]);

        $loader = $this->buildLoader([$task::class => $task]);

        $this->expectException(InvalidConfigurationException::class);
        $this->expectExceptionMessage('sometimes');

        $loader->loadFromYamlFiles([$file]);
    }

    public function testThrowsOnNonIntegerPriority(): void
    {
        $task = $this->createStubTask();
        $file = $this->writeYaml([
            'bad_prio' => ['run' => 'one_time', 'priority' => 'high', 'service' => $task::class],
        ]);

        $loader = $this->buildLoader([$task::class => $task]);

        $this->expectException(InvalidConfigurationException::class);
        $this->expectExceptionMessage('priority');

        $loader->loadFromYamlFiles([$file]);
    }

    public function testThrowsWhenServiceMissingForOneTimeMode(): void
    {
        $file = $this->writeYaml([
            'no_service' => ['run' => 'one_time'],
        ]);

        $loader = $this->buildLoader([]);

        $this->expectException(InvalidConfigurationException::class);
        $this->expectExceptionMessage('"service" is required');

        $loader->loadFromYamlFiles([$file]);
    }

    public function testThrowsWhenServiceNotRegistered(): void
    {
        $file = $this->writeYaml([
            'unknown_service' => ['run' => 'one_time', 'service' => 'No\\Such\\Class'],
        ]);

        $loader = $this->buildLoader([]);

        $this->expectException(PostDeployTaskConfigException::class);
        $this->expectExceptionMessage('not registered as a post-deploy task');

        $loader->loadFromYamlFiles([$file]);
    }

    public function testThrowsOnInvalidTaskKey(): void
    {
        $task = $this->createStubTask();
        $file = $this->writeYaml([
            'BadName' => ['run' => 'one_time', 'service' => $task::class],
        ]);

        $loader = $this->buildLoader([$task::class => $task]);

        $this->expectException(InvalidConfigurationException::class);
        $this->expectExceptionMessage('snake_case');

        $loader->loadFromYamlFiles([$file]);
    }

    public function testThrowsWhenRootIsList(): void
    {
        $tempFile = tempnam(sys_get_temp_dir(), 'post-deploy-tasks-test-') . '.yaml';
        $this->tempFiles[] = $tempFile;
        file_put_contents($tempFile, "- name: foo\n  run: never\n");

        $loader = $this->buildLoader([]);

        $this->expectException(InvalidConfigurationException::class);
        $this->expectExceptionMessage('mapping at the root keyed by task name');

        $loader->loadFromYamlFiles([$tempFile]);
    }

    public function testEmptyFilesResultInNoDescriptors(): void
    {
        $frameworkFile = $this->writeYaml([]);
        $appFile = $this->writeYaml([]);

        $loader = $this->buildLoader([]);

        $this->assertSame([], $loader->loadFromYamlFiles([$frameworkFile, $appFile])->getDescriptors());
    }

    public function testThrowsWhenFileDoesNotExist(): void
    {
        $loader = $this->buildLoader([]);

        $this->expectException(FileNotFoundException::class);

        $loader->loadFromYamlFiles(['/no/such/file.yaml']);
    }

    /**
     * @param array<string, array<string, mixed>> $entries
     */
    private function writeYaml(array $entries): string
    {
        $tempFile = tempnam(sys_get_temp_dir(), 'post-deploy-tasks-test-') . '.yaml';
        $this->tempFiles[] = $tempFile;
        $content = $entries === [] ? "{}\n" : Yaml::dump($entries, 4, 2);
        file_put_contents($tempFile, $content);

        return $tempFile;
    }

    private static int $stubCounter = 0;

    private function createStubTask(): PostDeployTaskInterface
    {
        return $this->getMockBuilder(PostDeployTaskInterface::class)
            ->setMockClassName('PostDeployTaskConfigLoaderTestStub_' . self::$stubCounter++)
            ->getMock();
    }

    /**
     * @param array<string, \Shopsys\FrameworkBundle\Component\PostDeploy\Task\PostDeployTaskInterface> $services
     */
    private function buildLoader(array $services): PostDeployTaskConfigLoader
    {
        $factories = [];

        foreach ($services as $id => $service) {
            $factories[$id] = static fn () => $service;
        }

        return new PostDeployTaskConfigLoader(
            new Filesystem(),
            new ServiceLocator($factories),
            new PostDeployTaskRunEnum(),
        );
    }
}
