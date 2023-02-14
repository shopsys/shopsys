<?php

declare(strict_types=1);

namespace Shopsys\Releaser\ReleaseWorker\AfterRelease;

use PharIo\Version\Version;
use Shopsys\Releaser\ReleaseWorker\AbstractShopsysReleaseWorker;
use Shopsys\Releaser\Stage;

final class CheckDocsReleaseWorker extends AbstractShopsysReleaseWorker
{
    /**
     * @param \PharIo\Version\Version $version
     * @param string $initialBranchName
     * @return string
     */
    public function getDescription(Version $version, string $initialBranchName = AbstractShopsysReleaseWorker::MAIN_BRANCH_NAME): string
    {
        return '[Manually] Check documentation is released, version is present and latest highest version is set as default';
    }

    /**
     * @param \PharIo\Version\Version $version
     * @param string $initialBranchName
     */
    public function work(Version $version, string $initialBranchName = AbstractShopsysReleaseWorker::MAIN_BRANCH_NAME): void
    {
        $this->symfonyStyle->note('If you are releasing major or minor version, check that this version is present on https://docs.shopsys.com/');
        $this->symfonyStyle->note('Also check that current highest major or minor version is set as default on https://readthedocs.org/dashboard/shopsys-knowledge-base/advanced/');
        $this->symfonyStyle->note('For login to Read the Docs you can use Shopsys Bot Github account present in BitWarden Vault.');
        $this->symfonyStyle->note('Authentication code is also present on page with password.');

        $this->symfonyStyle->confirm('Confirm that documentation is set correctly.');
    }

    /**
     * @return string
     */
    public function getStage(): string
    {
        return Stage::AFTER_RELEASE;
    }
}
