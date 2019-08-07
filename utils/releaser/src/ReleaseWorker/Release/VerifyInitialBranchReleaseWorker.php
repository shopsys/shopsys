<?php

declare(strict_types=1);

namespace Shopsys\Releaser\ReleaseWorker\Release;

use Shopsys\Releaser\ReleaseWorker\AbstractVerifyInitialBranchReleaseWorker;
use Shopsys\Releaser\Stage;

final class VerifyInitialBranchReleaseWorker extends AbstractVerifyInitialBranchReleaseWorker
{
    /**
     * @return int
     */
    public function getPriority(): int
    {
        return 710;
    }

    /**
     * @return string
     */
    public function getStage(): string
    {
        return Stage::RELEASE;
    }
}
