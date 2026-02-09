<?php

declare(strict_types=1);

namespace Shopsys\Releaser\ReleaseWorker\ReleaseCandidate;

use Override;
use PharIo\Version\Version;
use Shopsys\Releaser\ReleaseWorker\AbstractShopsysReleaseWorker;
use Shopsys\Releaser\Stage;

final class VerifyCliIsRunningReleaseWorker extends AbstractShopsysReleaseWorker
{
    #[Override]
    public function getDescription(
        Version $version,
        string $initialBranchName = AbstractShopsysReleaseWorker::MAIN_BRANCH_NAME,
    ): string {
        return '[Manually] Verify that Shopsys Cli is able to run on the new version';
    }

    #[Override]
    public function work(
        Version $version,
        string $initialBranchName = AbstractShopsysReleaseWorker::MAIN_BRANCH_NAME,
    ): void {
        $this->symfonyStyle->note([
            '1) Install composer dependencies in packages/cli',
            '2) In some temporary directory use Cli to init a project',
            sprintf('path-to-project/packages/cli init rc-project -b %s', $this->currentBranchName),
            '3) Verify it finishes without errors.',
        ]);
        $this->confirm('Confirm the Shopsys Cli is able to run on the new version.');
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
