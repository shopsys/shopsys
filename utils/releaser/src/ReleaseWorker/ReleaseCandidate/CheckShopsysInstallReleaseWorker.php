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
    protected function getCheckoutTarget(Version $version): string
    {
        return $this->createBranchName($version);
    }
}
