<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\PostDeploy\Task;

use Shopsys\FrameworkBundle\Component\PostDeploy\Task\Exception\PostDeployTaskFailedException;
use Shopsys\FrameworkBundle\Model\PostDeploy\OneTimePostDeployTaskFacade;
use Symfony\Component\Console\Style\SymfonyStyle;
use Throwable;

class PostDeployTaskRunnerFacade
{
    public function __construct(
        protected readonly PostDeployTaskConfig $postDeployTaskConfig,
        protected readonly OneTimePostDeployTaskFacade $oneTimePostDeployTaskFacade,
    ) {
    }

    public function run(SymfonyStyle $style): void
    {
        $descriptors = $this->postDeployTaskConfig->getDescriptors();

        if ($descriptors === []) {
            $style->success('No post-deploy tasks registered.');

            return;
        }

        $executedNames = array_flip($this->oneTimePostDeployTaskFacade->getAllNames());
        $oneTimeRunCount = 0;
        $oneTimeSkippedCount = 0;
        $alwaysRunCount = 0;
        $neverSkippedCount = 0;

        foreach ($descriptors as $descriptor) {
            if ($descriptor->run === PostDeployTaskRunEnum::NEVER) {
                $style->writeln(sprintf('<comment>skipped (run=never): %s</comment>', $descriptor->name));
                $neverSkippedCount++;

                continue;
            }

            if ($descriptor->run === PostDeployTaskRunEnum::ONE_TIME && isset($executedNames[$descriptor->name])) {
                $style->writeln(sprintf('<info>skipped (already executed): %s</info>', $descriptor->name));
                $oneTimeSkippedCount++;

                continue;
            }

            $style->writeln(sprintf(
                '<info>running (%s, priority=%d): %s</info>',
                $descriptor->run,
                $descriptor->priority,
                $descriptor->name,
            ));
            $this->executeDescriptor($descriptor, $style);

            if ($descriptor->run === PostDeployTaskRunEnum::ONE_TIME) {
                $oneTimeRunCount++;
            } else {
                $alwaysRunCount++;
            }
        }

        $style->success(sprintf(
            'Post-deploy tasks finished. one_time: %d run, %d skipped (already executed); always: %d run; never: %d skipped.',
            $oneTimeRunCount,
            $oneTimeSkippedCount,
            $alwaysRunCount,
            $neverSkippedCount,
        ));
    }

    protected function executeDescriptor(PostDeployTaskDescriptor $descriptor, SymfonyStyle $style): void
    {
        try {
            $descriptor->task->run($style);
        } catch (Throwable $exception) {
            throw new PostDeployTaskFailedException($descriptor->name, $descriptor->run, $exception);
        }

        if ($descriptor->run === PostDeployTaskRunEnum::ONE_TIME) {
            $this->oneTimePostDeployTaskFacade->markExecuted($descriptor->name);
        }
    }
}
