<?php

declare(strict_types=1);

namespace Shopsys\Releaser\ReleaseWorker;

use Override;
use PharIo\Version\Version;
use Shopsys\Releaser\GithubActions\GithubActionsStatusReporter;
use Shopsys\Releaser\Wait\GithubActionsRunSucceeded;

/**
 * @see https://docs.github.com/en/rest/actions/workflows
 */
abstract class AbstractCheckPackagesGithubActionsBuildsReleaseWorker extends AbstractShopsysReleaseWorker
{
    /**
     * @var string
     */
    private const string STATUS_SUCCESS = 'success';

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

        $statusForPackages = $this->githubActionsStatusReporter->getStatusForPackagesByOrganizationAndBranch(
            'shopsys',
            $initialBranchName,
            $githubToken,
        );

        $isPassing = true;

        foreach ($statusForPackages as $package => $status) {
            if ($status === self::STATUS_SUCCESS) {
                $this->symfonyStyle->note(sprintf('"%s" package is passing', $package));
            } else {
                $isPassing = false;
                $this->symfonyStyle->error(sprintf(
                    '"%s" package is failing. Go check why:%s%s',
                    $package,
                    PHP_EOL,
                    sprintf('https://github.com/%s/actions', $package),
                ));
            }
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
