<?php

declare(strict_types=1);

namespace Shopsys\Releaser\ReleaseWorker\ReleaseCandidate;

use Shopsys\Releaser\ReleaseWorker\AbstractCheckPackagesGithubActionsBuildsReleaseWorker;
use Shopsys\Releaser\Stage;

final class CheckPackagesGithubActionsBuildsReleaseWorker extends AbstractCheckPackagesGithubActionsBuildsReleaseWorker
{
    /**
     * @return string[]
     */
    protected function getAllowedStages(): array
    {
        return [Stage::RELEASE_CANDIDATE];
    }

    /**
     * @return string
     */
    protected function getBranchName(): string
    {
        return $this->currentBranchName;
    }
}
