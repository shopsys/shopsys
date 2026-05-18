<?php

declare(strict_types=1);

namespace Shopsys\Releaser\ReleaseWorker;

use Override;
use PharIo\Version\Version;
use Shopsys\Releaser\GithubActions\GithubActionsStatusReporter;
use Shopsys\Releaser\Wait\GithubActionsRunSucceeded;
use Throwable;

/**
 * @see https://docs.github.com/en/rest/actions/workflows
 */
abstract class AbstractCheckPackagesGithubActionsBuildsReleaseWorker extends AbstractShopsysReleaseWorker
{
    public function __construct(private readonly GithubActionsStatusReporter $githubActionsStatusReporter)
    {
    }

    #[Override]
    public function getDescription(
        Version $version,
        string $initialBranchName = AbstractShopsysReleaseWorker::MAIN_BRANCH_NAME,
    ): string {
        return 'Check GitHub Actions build status for all packages';
    }

    abstract protected function getBranchName(): string;

    #[Override]
    public function work(
        Version $version,
        string $initialBranchName = AbstractShopsysReleaseWorker::MAIN_BRANCH_NAME,
    ): void {
        $githubToken = $this->resolveGithubToken();

        try {
            $statusForPackages = $this->githubActionsStatusReporter->getStatusForPackagesByOrganizationAndBranch(
                'shopsys',
                $initialBranchName,
                $githubToken,
            );
        } catch (Throwable $throwable) {
            $this->symfonyStyle->warning(sprintf(
                'Unable to read GitHub Actions status yet: %s',
                $throwable->getMessage(),
            ));
            $statusForPackages = [];
        }

        $isPassing = true;

        foreach ($statusForPackages as $package => $status) {
            if ($status === GithubActionsStatusReporter::STATUS_SUCCESS) {
                $this->symfonyStyle->note(sprintf('"%s" package is passing', $package));

                continue;
            }

            $isPassing = false;

            if ($status === GithubActionsStatusReporter::STATUS_PENDING) {
                $this->symfonyStyle->note(sprintf('"%s" package is still pending', $package));

                continue;
            }

            $this->symfonyStyle->error(sprintf(
                '"%s" package is failing (%s). Go check why:%s%s',
                $package,
                $status,
                PHP_EOL,
                sprintf('https://github.com/%s/actions', $package),
            ));
        }

        if (count($statusForPackages) === 0) {
            $this->symfonyStyle->warning('No status was reported yet; will poll.');
            $isPassing = false;
        }

        if ($isPassing === true) {
            $this->success();

            return;
        }

        $this->waitFor(new GithubActionsRunSucceeded(
            $this->githubActionsStatusReporter,
            'shopsys',
            $initialBranchName,
            $githubToken,
        ));

        $this->success();
    }
}
