<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\PostDeploy\Task;

class PostDeployTaskConfig
{
    /**
     * @param \Shopsys\FrameworkBundle\Component\PostDeploy\Task\PostDeployTaskDescriptor[] $descriptors
     */
    public function __construct(
        protected readonly array $descriptors,
    ) {
    }

    /**
     * @return \Shopsys\FrameworkBundle\Component\PostDeploy\Task\PostDeployTaskDescriptor[]
     */
    public function getDescriptors(): array
    {
        return $this->descriptors;
    }
}
