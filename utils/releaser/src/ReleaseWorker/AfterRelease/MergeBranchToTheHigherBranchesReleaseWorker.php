<?php

namespace Shopsys\Releaser\ReleaseWorker\AfterRelease;

use PharIo\Version\Version;
use Shopsys\Releaser\ReleaseWorker\AbstractShopsysReleaseWorker;
use Shopsys\Releaser\Stage;

final class MergeBranchToTheHigherBranchesReleaseWorker extends AbstractShopsysReleaseWorker
{
    /**
     * @param \PharIo\Version\Version $version
     * @return string
     */
    public function getDescription(Version $version): string
    {
        return '[Manually] Merge the branch with the release to the higher development branches.';
    }

    /**
     * Higher first
     * @return int
     */
    public function getPriority(): int
    {
        return 13;
    }

    /**
     * @param \PharIo\Version\Version $version
     */
    public function work(Version $version): void
    {
        $this->symfonyStyle->note('You need to gradually merge the branch with the new release to all the higher development branches.');
        $this->symfonyStyle->note('E.g. when releasing "7.3.x" version, you need to merge the "7.3" branch to the master branch, and then merge the master branch to the "9.0" branch provided current release series is "8.0"');
        $this->symfonyStyle->note('When merging to the higher branch, check the docs and fix the links if necessary. Articles and files should be always linked in the corresponding version, see https://docs.shopsys.com/en/latest/contributing/guidelines-for-writing-documentation/.');
        $this->confirm('Confirm the branch with the released version is merged to all the higher development branches and the links in the docs are fixed.');
    }

    /**
     * @return string
     */
    public function getStage(): string
    {
        return Stage::AFTER_RELEASE;
    }
}
