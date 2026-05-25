<?php

declare(strict_types=1);

namespace Shopsys\Releaser\ReleaseWorker\ReleaseCandidate;

use Override;
use Shopsys\Releaser\ReleaseWorker\AbstractCheckPackagesGithubActionsBuildsReleaseWorker;
use Shopsys\Releaser\Stage;

final class CheckPackagesGithubActionsBuildsAfterSplitReleaseWorker extends AbstractCheckPackagesGithubActionsBuildsReleaseWorker
{
    /**
     * @return string[]
     */
    #[Override]
    protected function getAllowedStages(): array
    {
        return [Stage::RELEASE_CANDIDATE];
    }

    #[Override]
    protected function getBranchName(): string
    {
        return $this->currentBranchName;
    }
}
