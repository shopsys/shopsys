<?php

declare(strict_types=1);

namespace Shopsys\Releaser\ReleaseWorker\Release;

use Override;
use PharIo\Version\Version;
use Shopsys\Releaser\GithubActions\GithubActionsStatusReporter;
use Shopsys\Releaser\ReleaseWorker\AbstractShopsysReleaseWorker;
use Shopsys\Releaser\Stage;
use Shopsys\Releaser\Wait\GithubActionsWorkflowSucceeded;

final class MergeReleaseCandidateBranchReleaseWorker extends AbstractShopsysReleaseWorker
{
    private const string SPLIT_WORKFLOW_FILE = 'monorepo-split.yaml';

    public function __construct(
        private readonly GithubActionsStatusReporter $githubActionsStatusReporter,
    ) {
    }

    #[Override]
    public function getDescription(
        Version $version,
        string $initialBranchName = AbstractShopsysReleaseWorker::MAIN_BRANCH_NAME,
    ): string {
        return sprintf(
            '[Manually] Merge "%s" branch into "%s"',
            $this->createBranchName($version),
            $initialBranchName,
        );
    }

    #[Override]
    public function work(
        Version $version,
        string $initialBranchName = AbstractShopsysReleaseWorker::MAIN_BRANCH_NAME,
    ): void {
        $this->symfonyStyle->note('You need to create a merge commit locally.');
        $this->symfonyStyle->warning(sprintf(
            'Do not forget to push the "%s" branch!',
            $initialBranchName,
        ));
        $this->confirm(
            sprintf(
                'Confirm "%s" branch was merged and pushed to "%s"',
                $this->createBranchName($version),
                $initialBranchName,
            ),
        );

        $this->symfonyStyle->note(
            'Waiting for the automatic split via https://github.com/shopsys/shopsys/actions/workflows/monorepo-split.yaml',
        );

        $this->waitFor(new GithubActionsWorkflowSucceeded(
            $this->githubActionsStatusReporter,
            AbstractShopsysReleaseWorker::ORGANIZATION,
            AbstractShopsysReleaseWorker::MONOREPO_REPOSITORY,
            $initialBranchName,
            self::SPLIT_WORKFLOW_FILE,
            $this->resolveGithubToken(),
        ));
    }

    /**
     * @return string[]
     */
    #[Override]
    protected function getAllowedStages(): array
    {
        return [Stage::RELEASE];
    }
}
