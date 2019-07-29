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
     * @return string
     */
    public function getDescription(Version $version): string
    {
        return 'Remove lock files from the repository, commit the change, and [Manually] push';
    }

    /**
     * Higher first
     * @return int
     */
    public function getPriority(): int
    {
        return 180;
    }

    /**
     * @param \PharIo\Version\Version $version
     */
    public function work(Version $version): void
    {
        $this->processRunner->run('git rm project-base/composer.lock');
        $this->processRunner->run('git rm project-base/package-lock.json');
        $this->commit('removed locked versions of dependencies for unreleased version');

        if ($this->initialBranchName === 'master') {
            $this->symfonyStyle->note('You need to push the master branch manually, however, you have to wait until the previous (tagged) master build is finished on Heimdall. Otherwise, master-project-base would have never been built from the source codes where there are dependencies on the tagged versions of shopsys packages.');
            $this->confirm('Confirm you have waited long enough and then pushed the master branch.');
        } else {
            $this->symfonyStyle->note(sprintf('You need to push the "%s" branch manually', $this->initialBranchName));
            $this->confirm(sprintf('Confirm you have pushed the "%s "branch.', $this->initialBranchName));
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
