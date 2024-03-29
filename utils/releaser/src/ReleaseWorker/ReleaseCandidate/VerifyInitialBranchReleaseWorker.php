<?php

declare(strict_types=1);

namespace Shopsys\Releaser\ReleaseWorker\ReleaseCandidate;

use Shopsys\Releaser\ReleaseWorker\AbstractVerifyInitialBranchReleaseWorker;
use Shopsys\Releaser\Stage;

final class VerifyInitialBranchReleaseWorker extends AbstractVerifyInitialBranchReleaseWorker
{
    /**
     * @return string
     */
    public function getStage(): string
    {
        return Stage::RELEASE_CANDIDATE;
    }
}
