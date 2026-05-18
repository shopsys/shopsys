<?php

declare(strict_types=1);

namespace Tests\FrameworkBundle\Unit\Component\PostDeploy\Task;

use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Shopsys\FrameworkBundle\Component\PostDeploy\Task\Exception\PostDeployTaskFailedException;
use Shopsys\FrameworkBundle\Component\PostDeploy\Task\PostDeployTaskConfig;
use Shopsys\FrameworkBundle\Component\PostDeploy\Task\PostDeployTaskDescriptor;
use Shopsys\FrameworkBundle\Component\PostDeploy\Task\PostDeployTaskInterface;
use Shopsys\FrameworkBundle\Component\PostDeploy\Task\PostDeployTaskRunEnum;
use Shopsys\FrameworkBundle\Component\PostDeploy\Task\PostDeployTaskRunnerFacade;
use Shopsys\FrameworkBundle\Model\PostDeploy\OneTimePostDeployTaskFacade;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AllowMockObjectsWithoutExpectations]
class PostDeployTaskRunnerFacadeTest extends TestCase
{
    public function testSkipsAlreadyExecutedOneTimeTask(): void
    {
        $task = $this->createMock(PostDeployTaskInterface::class);
        $task->expects($this->never())->method('run');

        $config = new PostDeployTaskConfig([
            $this->makeDescriptor('already_done', PostDeployTaskRunEnum::ONE_TIME, $task),
        ]);

        $oneTimePostDeployTaskFacade = $this->createMock(OneTimePostDeployTaskFacade::class);
        $oneTimePostDeployTaskFacade->method('getAllNames')->willReturn(['already_done']);
        $oneTimePostDeployTaskFacade->expects($this->never())->method('markExecuted');

        $facade = new PostDeployTaskRunnerFacade($config, $oneTimePostDeployTaskFacade);
        $facade->run($this->makeStyle());
    }

    public function testMarksOneTimeTaskAsExecutedOnSuccess(): void
    {
        $task = $this->createMock(PostDeployTaskInterface::class);
        $task->expects($this->once())->method('run');

        $config = new PostDeployTaskConfig([
            $this->makeDescriptor('first_run', PostDeployTaskRunEnum::ONE_TIME, $task),
        ]);

        $oneTimePostDeployTaskFacade = $this->createMock(OneTimePostDeployTaskFacade::class);
        $oneTimePostDeployTaskFacade->method('getAllNames')->willReturn([]);
        $oneTimePostDeployTaskFacade->expects($this->once())->method('markExecuted')->with('first_run');

        $facade = new PostDeployTaskRunnerFacade($config, $oneTimePostDeployTaskFacade);
        $facade->run($this->makeStyle());
    }

    public function testRunsAlwaysModeWithoutTracking(): void
    {
        $task = $this->createMock(PostDeployTaskInterface::class);
        $task->expects($this->once())->method('run');

        $config = new PostDeployTaskConfig([
            $this->makeDescriptor('every_time', PostDeployTaskRunEnum::ALWAYS, $task),
        ]);

        $oneTimePostDeployTaskFacade = $this->createMock(OneTimePostDeployTaskFacade::class);
        $oneTimePostDeployTaskFacade->method('getAllNames')->willReturn([]);
        $oneTimePostDeployTaskFacade->expects($this->never())->method('markExecuted');

        $facade = new PostDeployTaskRunnerFacade($config, $oneTimePostDeployTaskFacade);
        $facade->run($this->makeStyle());
    }

    public function testSkipsNeverWithoutExecution(): void
    {
        $config = new PostDeployTaskConfig([
            $this->makeDescriptor('disabled', PostDeployTaskRunEnum::NEVER, null),
        ]);

        $oneTimePostDeployTaskFacade = $this->createMock(OneTimePostDeployTaskFacade::class);
        $oneTimePostDeployTaskFacade->method('getAllNames')->willReturn([]);
        $oneTimePostDeployTaskFacade->expects($this->never())->method('markExecuted');

        $facade = new PostDeployTaskRunnerFacade($config, $oneTimePostDeployTaskFacade);
        $facade->run($this->makeStyle());
    }

    public function testHaltsOnFailureWithoutMarkingFailedTask(): void
    {
        $failingTask = $this->createMock(PostDeployTaskInterface::class);
        $failingTask->method('run')->willThrowException(new RuntimeException('boom'));

        $laterTask = $this->createMock(PostDeployTaskInterface::class);
        $laterTask->expects($this->never())->method('run');

        $config = new PostDeployTaskConfig([
            $this->makeDescriptor('failing', PostDeployTaskRunEnum::ONE_TIME, $failingTask, priority: 100),
            $this->makeDescriptor('later', PostDeployTaskRunEnum::ALWAYS, $laterTask, priority: 0),
        ]);

        $oneTimePostDeployTaskFacade = $this->createMock(OneTimePostDeployTaskFacade::class);
        $oneTimePostDeployTaskFacade->method('getAllNames')->willReturn([]);
        $oneTimePostDeployTaskFacade->expects($this->never())->method('markExecuted');

        $facade = new PostDeployTaskRunnerFacade($config, $oneTimePostDeployTaskFacade);

        $this->expectException(PostDeployTaskFailedException::class);
        $this->expectExceptionMessage('Post-deploy task "failing" (run=one_time) failed');

        $facade->run($this->makeStyle());
    }

    private function makeDescriptor(
        string $name,
        string $run,
        ?PostDeployTaskInterface $task,
        int $priority = 0,
    ): PostDeployTaskDescriptor {
        return new PostDeployTaskDescriptor(
            name: $name,
            run: $run,
            priority: $priority,
            task: $task,
        );
    }

    private function makeStyle(): SymfonyStyle
    {
        return new SymfonyStyle(new ArrayInput([]), new BufferedOutput());
    }
}
