<?php

declare(strict_types=1);

namespace Shopsys\Releaser\ReleaseWorker\ReleaseCandidate;

use Override;
use PharIo\Version\Version;
use Shopsys\Releaser\ReleaseWorker\AbstractShopsysReleaseWorker;
use Shopsys\Releaser\Stage;

final class VerifyMinorUpgradeReleaseWorker extends AbstractShopsysReleaseWorker
{
    #[Override]
    public function getDescription(
        Version $version,
        string $initialBranchName = AbstractShopsysReleaseWorker::MAIN_BRANCH_NAME,
    ): string {
        return '[Manually] Verify there are no BC-breaks when releasing a minor version';
    }

    #[Override]
    public function work(
        Version $version,
        string $initialBranchName = AbstractShopsysReleaseWorker::MAIN_BRANCH_NAME,
    ): void {
        $this->symfonyStyle->note('When releasing a minor version, you need to verify there are no BC-breaks. Suggested steps:
        - use https://github.com/Roave/BackwardCompatibilityCheck tool
        - manually examine the output and validate whether the reported changes are considered a BC Break by us');
        $this->confirm('Confirm the minor version does not contain any BC-breaks.');
    }

    /**
     * @return string[]
     */
    #[Override]
    protected function getAllowedStages(): array
    {
        return [Stage::RELEASE_CANDIDATE];
    }
}
