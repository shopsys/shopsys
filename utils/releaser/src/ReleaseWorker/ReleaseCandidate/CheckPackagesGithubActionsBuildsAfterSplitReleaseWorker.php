<?php

declare(strict_types=1);

namespace Shopsys\Releaser\ReleaseWorker\ReleaseCandidate;

use Override;
use PharIo\Version\Version;
use Shopsys\Releaser\ReleaseWorker\AbstractCheckPackagesGithubActionsBuildsReleaseWorker;
use Shopsys\Releaser\ReleaseWorker\AbstractShopsysReleaseWorker;
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

    #[Override]
    public function work(
        Version $version,
        string $initialBranchName = AbstractShopsysReleaseWorker::MAIN_BRANCH_NAME,
    ): void {
        $this->confirm(
            sprintf(
                'Confirm that current branch (%s) is split and all packages actions are finished.',
                $this->getBranchName(),
            ),
        );

        parent::work($version, $initialBranchName);
    }
}
