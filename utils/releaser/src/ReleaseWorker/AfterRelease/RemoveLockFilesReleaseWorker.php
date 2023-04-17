<?php

declare(strict_types=1);

namespace Shopsys\Releaser\ReleaseWorker\AfterRelease;

use PharIo\Version\Version;
use Shopsys\Releaser\ReleaseWorker\AbstractShopsysReleaseWorker;
use Shopsys\Releaser\Stage;

final class RemoveLockFilesReleaseWorker extends AbstractShopsysReleaseWorker
{
    /**
     * @param \PharIo\Version\Version $version
     * @param string $initialBranchName
     * @return string
     */
    public function getDescription(Version $version, string $initialBranchName = AbstractShopsysReleaseWorker::MAIN_BRANCH_NAME): string
    {
        return 'Remove lock files from the repository, commit the change, and [Manually] push';
    }

    /**
     * @param \PharIo\Version\Version $version
     * @param string $initialBranchName
     */
    public function work(Version $version, string $initialBranchName = AbstractShopsysReleaseWorker::MAIN_BRANCH_NAME): void
    {
        $this->processRunner->run('git rm project-base/composer.lock --ignore-unmatch');
        $this->processRunner->run('git rm project-base/package-lock.json --ignore-unmatch');
        $this->processRunner->run('git rm project-base/migrations-lock.yml --ignore-unmatch');
        // symfony.lock is not deleted as its removal would lead to reset of Symfony Flex
        $this->commit('removed locked versions of dependencies for unreleased version');

        if ($this->currentBranchName === AbstractShopsysReleaseWorker::MAIN_BRANCH_NAME) {
            $this->symfonyStyle->note(
                'You need to push the master branch manually, however, you have to wait until the previous (tagged) master build is finished on Heimdall. Otherwise, master-project-base would have never been built from the source codes where there are dependencies on the tagged versions of Shopsys packages.'
            );
            $this->confirm('Confirm you have waited long enough and then pushed the master branch.');
        } else {
            $this->symfonyStyle->note(sprintf('You need to push the "%s" branch manually', $this->currentBranchName));
            $this->confirm(sprintf('Confirm you have pushed the "%s "branch.', $this->currentBranchName));
        }
    }

    /**
     * @return string
     */
    public function getStage(): string
    {
        return Stage::AFTER_RELEASE;
    }
}
