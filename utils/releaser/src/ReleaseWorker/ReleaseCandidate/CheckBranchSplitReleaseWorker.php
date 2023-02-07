<?php

declare(strict_types=1);

namespace Shopsys\Releaser\ReleaseWorker\ReleaseCandidate;

use PharIo\Version\Version;
use Shopsys\Releaser\ReleaseWorker\AbstractShopsysReleaseWorker;
use Shopsys\Releaser\Stage;

final class CheckBranchSplitReleaseWorker extends AbstractShopsysReleaseWorker
{
    /**
     * @param \PharIo\Version\Version $version
     * @return string
     */
    public function getDescription(Version $version): string
    {
        return '[Manually] Make sure that branch of version you are now releasing is split';
    }

    /**
     * @param \PharIo\Version\Version $version
     */
    public function work(Version $version): void
    {
        $this->symfonyStyle->note('Branches other than master branch are not automatically split.');
        $this->symfonyStyle->note('In next step you will be asked to check status on Github actions, in order to do that you must now ensure, that the branch of version you are releasing is correctly split.');
        $this->symfonyStyle->note('If branch is not split, do it using tool-monorepo-force-split-branch on Heimdall');
        $this->confirm('Continue after the branch is split');
    }

    /**
     * @return string
     */
    public function getStage(): string
    {
        return Stage::RELEASE_CANDIDATE;
    }
}
