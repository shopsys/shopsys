<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\PostDeploy\Task;

class PostDeployTaskDescriptor
{
    public function __construct(
        public readonly string $name,
        public readonly string $run,
        public readonly int $priority,
        public readonly ?PostDeployTaskInterface $task,
    ) {
    }
}
