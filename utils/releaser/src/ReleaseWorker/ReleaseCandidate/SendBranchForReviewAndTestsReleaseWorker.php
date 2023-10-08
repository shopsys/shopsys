<?php

declare(strict_types=1);

namespace Shopsys\Releaser\ReleaseWorker\ReleaseCandidate;

use PharIo\Version\Version;
use Shopsys\Releaser\ReleaseWorker\AbstractShopsysReleaseWorker;
use Shopsys\Releaser\Stage;

final class SendBranchForReviewAndTestsReleaseWorker extends AbstractShopsysReleaseWorker
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
        return '[Manually] Send the branch for review and tests';
    }

    /**
     * @param \PharIo\Version\Version $version
     * @param string $initialBranchName
     */
    public function work(
        Version $version,
        string $initialBranchName = AbstractShopsysReleaseWorker::MAIN_BRANCH_NAME,
    ): void {
        $this->symfonyStyle->note(['Keep in mind that if there are any notes from the code review or tests, you need to:',
            ' - address them',
            ' - commit and push the fixes',
            ' - force-split your branch using GitHub Actions',
            '       https://github.com/shopsys/shopsys/actions/workflows/monorepo-force-split-branch.yaml',
            ' - run the tests again',
            ' - check project-base build on GitHub Actions',
            '       https://github.com/shopsys/project-base/actions/workflows/run-checks-tests.yaml',
        ]);
        $this->confirm('Confirm the branch is sent to code-review');
    }

    /**
     * @return string
     */
    public function getStage(): string
    {
        return Stage::RELEASE_CANDIDATE;
    }
}
