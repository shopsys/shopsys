<?php

declare(strict_types=1);

namespace Shopsys\Releaser\ReleaseWorker;

use Override;
use PharIo\Version\Version;
use Shopsys\Releaser\Stage;

final class CheckUncommittedChangesReleaseWorker extends AbstractShopsysReleaseWorker
{
    #[Override]
    public function getDescription(
        Version $version,
        string $initialBranchName = AbstractShopsysReleaseWorker::MAIN_BRANCH_NAME,
    ): string {
        return 'Check the repository for any uncommitted changes';
    }

    #[Override]
    public function work(
        Version $version,
        string $initialBranchName = AbstractShopsysReleaseWorker::MAIN_BRANCH_NAME,
    ): void {
        if (!$this->isGitWorkingTreeEmpty()) {
            $this->symfonyStyle->warning(
                'There are some uncommitted changes in your repository (see the result of "git status" command), please resolve them before you continue with the release process.',
            );
            $this->confirm('Confirm that you have resolved all uncommitted files and your working tree is empty now.');
        } else {
            $this->success();
        }
    }

    /**
     * @return string[]
     */
    #[Override]
    protected function getAllowedStages(): array
    {
        return Stage::getAllStages();
    }
}
