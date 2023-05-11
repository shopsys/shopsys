<?php

declare(strict_types=1);

namespace Shopsys\Releaser\ReleaseWorker\AfterRelease;

use PharIo\Version\Version;
use Shopsys\Releaser\ReleaseWorker\AbstractCheckPackagesGithubActionsBuildsReleaseWorker;
use Shopsys\Releaser\ReleaseWorker\AbstractShopsysReleaseWorker;
use Shopsys\Releaser\Stage;

final class CheckPackagesGithubActionsBuildsReleaseWorker extends AbstractCheckPackagesGithubActionsBuildsReleaseWorker
{
    private string $releasingBranchName;

    /**
     * @return string
     */
    public function getStage(): string
    {
        return Stage::AFTER_RELEASE;
    }

    /**
     * @return string
     */
    protected function getBranchName(): string
    {
        return $this->releasingBranchName;
    }

    /**
     * @param \PharIo\Version\Version $version
     * @param string $initialBranchName
     */
    public function work(Version $version, string $initialBranchName = AbstractShopsysReleaseWorker::MAIN_BRANCH_NAME): void
    {
        $this->releasingBranchName = $version->getOriginalString();

        parent::work($version);
    }
}
