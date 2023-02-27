<?php

declare(strict_types=1);

namespace Shopsys\Releaser\ReleaseWorker;

use PharIo\Version\Version;
use Shopsys\Releaser\GithubActions\GithubActionsStatusReporter;

/**
 * @see https://docs.github.com/en/rest/actions/workflows
 */
abstract class AbstractCheckPackagesGithubActionsBuildsReleaseWorker extends AbstractShopsysReleaseWorker
{
    /**
     * @var string
     */
    private const STATUS_SUCCESS = 'success';

    /**
     * @var \Shopsys\Releaser\GithubActions\GithubActionsStatusReporter
     */
    private GithubActionsStatusReporter $githubActionsStatusReporter;

    /**
     * @param \Shopsys\Releaser\GithubActions\GithubActionsStatusReporter $githubActionsStatusReporter
     */
    public function __construct(GithubActionsStatusReporter $githubActionsStatusReporter)
    {
        $this->githubActionsStatusReporter = $githubActionsStatusReporter;
    }

    /**
     * @param \PharIo\Version\Version $version
     * @param string $initialBranchName
     * @return string
     */
    public function getDescription(Version $version, string $initialBranchName = AbstractShopsysReleaseWorker::MAIN_BRANCH_NAME): string
    {
        return 'Check GitHub Actions build status for all packages';
    }

    /**
     * @return string
     */
    abstract protected function getBranchName(): string;

    /**
     * @param \PharIo\Version\Version $version
     * @param string $initialBranchName
     */
    public function work(Version $version, string $initialBranchName = AbstractShopsysReleaseWorker::MAIN_BRANCH_NAME): void
    {
        $this->symfonyStyle->note('It is necessary to set Github token before checking Github Actions builds');
        $githubToken = $this->symfonyStyle->ask(
            'Please enter no-scope Github token (https://github.com/settings/tokens/new)'
        );
        $statusForPackages = $this->githubActionsStatusReporter->getStatusForPackagesByOrganizationAndBranch(
            'shopsys',
            $initialBranchName,
            $githubToken
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
                    sprintf('https://github.com/%s/actions', $package)
                ));
            }
        }

        if (count($statusForPackages) === 0) {
            $this->symfonyStyle->warning('No status was reported, go rather check the builds manually');
            $isPassing = false;
        }

        if ($isPassing === false) {
            $this->confirm('Continue after packages are resolved');
        }
    }
}
