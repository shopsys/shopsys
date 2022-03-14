<?php

declare(strict_types=1);

namespace Shopsys\Releaser\ReleaseWorker\AfterRelease;

use Shopsys\Releaser\ReleaseWorker\AbstractVerifyInitialBranchReleaseWorker;
use Shopsys\Releaser\Stage;

final class VerifyInitialBranchReleaseWorker extends AbstractVerifyInitialBranchReleaseWorker
{
    /**
     * @return string
     */
    public function getStage(): string
    {
        return Stage::AFTER_RELEASE;
    }
}
