<?php

declare(strict_types=1);

namespace Shopsys\Releaser\ReleaseWorker;

use PharIo\Version\Version;

abstract class AbstractVerifyInitialBranchReleaseWorker extends AbstractShopsysReleaseWorker
{
    /**
     * @param \PharIo\Version\Version $version
     * @param string $initialBranchName
     * @return string
     */
    public function getDescription(
        Version $version,
        string $initialBranchName = AbstractShopsysReleaseWorker::MAIN_BRANCH_NAME,
    ): string {
        return 'Verify that you\'re releasing on the proper branch';
    }

    /**
     * @param \PharIo\Version\Version $version
     * @param string $initialBranchName
     */
    public function work(
        Version $version,
        string $initialBranchName = AbstractShopsysReleaseWorker::MAIN_BRANCH_NAME,
    ): void {
        $this->symfonyStyle->note(
            'It is important to perform the release process on the proper branch, i.e. on 7.3 branch when releasing 7.3.x patch version, and on master when releasing new minor version for the current release.',
        );
        $this->symfonyStyle->note(sprintf('Currently, you are on "%s" branch.', $this->currentBranchName));
        $this->confirm('Confirm you are on the proper branch.');
    }
}
