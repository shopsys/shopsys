<?php

declare(strict_types=1);

namespace Shopsys\Releaser\ReleaseWorker\ReleaseCandidate;

use Shopsys\Releaser\ReleaseWorker\AbstractCheckUncommittedChangesReleaseWorker;
use Shopsys\Releaser\Stage;

final class CheckUncommittedChangesReleaseWorker extends AbstractCheckUncommittedChangesReleaseWorker
{
    /**
     * @return string
     */
    public function getStage(): string
    {
        return Stage::RELEASE_CANDIDATE;
    }
}
