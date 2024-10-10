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
    public function getDescription(
        Version $version,
        string $initialBranchName = AbstractShopsysReleaseWorker::MAIN_BRANCH_NAME,
    ): string {
        return 'Remove lock files from the repository, commit the change, and [Manually] push';
    }

    /**
     * @param \PharIo\Version\Version $version
     * @param string $initialBranchName
     */
    public function work(
        Version $version,
        string $initialBranchName = AbstractShopsysReleaseWorker::MAIN_BRANCH_NAME,
    ): void {
        $this->processRunner->run('git rm project-base/app/composer.lock --ignore-unmatch');
        $this->processRunner->run('git rm project-base/app/package-lock.json --ignore-unmatch');
        $this->processRunner->run('git rm project-base/app/migrations-lock.yml --ignore-unmatch');
        // symfony.lock is not deleted as its removal would lead to reset of Symfony Flex
        $this->commit('removed locked versions of dependencies for unreleased version');

        $this->symfonyStyle->note(sprintf('You need to push the "%s" branch manually', $this->currentBranchName));
        $this->confirm(sprintf('Confirm you have pushed the "%s "branch.', $this->currentBranchName));
    }

    /**
     * @return string[]
     */
    protected function getAllowedStages(): array
    {
        return [Stage::AFTER_RELEASE];
    }
}
