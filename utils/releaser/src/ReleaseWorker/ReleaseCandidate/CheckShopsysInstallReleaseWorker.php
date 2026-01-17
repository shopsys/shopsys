<?php

declare(strict_types=1);

namespace Shopsys\Releaser\ReleaseWorker\ReleaseCandidate;

use Override;
use PharIo\Version\Version;
use Shopsys\Releaser\ReleaseWorker\AbstractCheckShopsysInstallReleaseWorker;
use Shopsys\Releaser\ReleaseWorker\AbstractShopsysReleaseWorker;
use Shopsys\Releaser\Stage;

final class CheckShopsysInstallReleaseWorker extends AbstractCheckShopsysInstallReleaseWorker
{
    #[Override]
    public function getDescription(
        Version $version,
        string $initialBranchName = AbstractShopsysReleaseWorker::MAIN_BRANCH_NAME,
    ): string {
        return '[Manually] Install Shopsys Platform (project-base) using installation guides on all supported operating systems.';
    }

    /**
     * @return string[]
     */
    #[Override]
    protected function getAllowedStages(): array
    {
        return [Stage::RELEASE_CANDIDATE];
    }

    #[Override]
    protected function writeInstructionsForProjectBasePreparation(Version $version): void
    {
        $branchName = $this->createBranchName($version);

        $this->symfonyStyle->note(sprintf(
            'Instructions for project base preparation:

git clone https://github.com/shopsys/project-base.git
cd project-base
git checkout %1$s
',
            $branchName,
        ));
    }
}
