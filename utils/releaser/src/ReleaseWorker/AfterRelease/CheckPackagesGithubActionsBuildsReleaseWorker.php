<?php

declare(strict_types=1);

namespace Shopsys\Releaser\ReleaseWorker\AfterRelease;

use Override;
use PharIo\Version\Version;
use Shopsys\Releaser\ReleaseWorker\AbstractCheckPackagesGithubActionsBuildsReleaseWorker;
use Shopsys\Releaser\ReleaseWorker\AbstractShopsysReleaseWorker;
use Shopsys\Releaser\Stage;

final class CheckPackagesGithubActionsBuildsReleaseWorker extends AbstractCheckPackagesGithubActionsBuildsReleaseWorker
{
    private string $releasingBranchName;

    /**
     * @return string[]
     */
    #[Override]
    protected function getAllowedStages(): array
    {
        return [Stage::AFTER_RELEASE];
    }

    #[Override]
    protected function getBranchName(): string
    {
        return $this->releasingBranchName;
    }

    #[Override]
    public function work(
        Version $version,
        string $initialBranchName = AbstractShopsysReleaseWorker::MAIN_BRANCH_NAME,
    ): void {
        $this->releasingBranchName = $version->getOriginalString();

        parent::work($version, $this->releasingBranchName);
    }
}
