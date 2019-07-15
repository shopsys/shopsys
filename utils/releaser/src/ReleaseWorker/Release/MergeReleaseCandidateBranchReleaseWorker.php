<?php

declare(strict_types=1);

namespace Shopsys\Releaser\ReleaseWorker\Release;

use PharIo\Version\Version;
use Shopsys\Releaser\ReleaseWorker\AbstractShopsysReleaseWorker;
use Shopsys\Releaser\Stage;

final class MergeReleaseCandidateBranchReleaseWorker extends AbstractShopsysReleaseWorker
{
    /**
     * @param \PharIo\Version\Version $version
     * @return string
     */
    public function getDescription(Version $version): string
    {
        return sprintf('[Manually] Merge "%s" branch into "%s"', $this->createBranchName($version), $this->initialBranchName);
    }

    /**
     * Higher first
     * @return int
     */
    public function getPriority(): int
    {
        return 650;
    }

    /**
     * @param \PharIo\Version\Version $version
     */
    public function work(Version $version): void
    {
        $this->symfonyStyle->note('You need to create a merge commit, see https://github.com/shopsys/shopsys/blob/master/docs/contributing/merging-on-github.md for detailed instructions.');
        $this->confirm(sprintf('Confirm "%s" branch was merged to "%s"', $this->createBranchName($version), $this->initialBranchName));
    }

    /**
     * @return string
     */
    public function getStage(): string
    {
        return Stage::RELEASE;
    }
}
