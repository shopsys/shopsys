<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\PostDeploy\Task\Exception;

use Exception;
use Throwable;

class PostDeployTaskFailedException extends Exception
{
    public function __construct(
        string $taskName,
        string $run,
        Throwable $previous,
    ) {
        parent::__construct(
            sprintf(
                'Post-deploy task "%s" (run=%s) failed: %s',
                $taskName,
                $run,
                $previous->getMessage(),
            ),
            0,
            $previous,
        );
    }
}
