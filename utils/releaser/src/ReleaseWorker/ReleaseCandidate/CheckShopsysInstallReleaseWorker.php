<?php

declare(strict_types=1);

namespace Shopsys\Releaser\ReleaseWorker\ReleaseCandidate;

use PharIo\Version\Version;
use Shopsys\Releaser\ReleaseWorker\AbstractCheckShopsysInstallReleaseWorker;
use Shopsys\Releaser\ReleaseWorker\AbstractShopsysReleaseWorker;
use Shopsys\Releaser\Stage;

final class CheckShopsysInstallReleaseWorker extends AbstractCheckShopsysInstallReleaseWorker
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
        return '[Manually] Install Shopsys Platform (project-base) using installation guides on all supported operating systems.';
    }

    /**
     * @return string
     */
    public function getStage(): string
    {
        return Stage::RELEASE_CANDIDATE;
    }

    /**
     * @param \PharIo\Version\Version $version
     * @param string $initialBranchName
     */
    public function work(
        Version $version,
        string $initialBranchName = AbstractShopsysReleaseWorker::MAIN_BRANCH_NAME,
    ): void {
        $branchName = $this->createBranchName($version);

        $this->symfonyStyle->note(sprintf(
            'Instructions for installation:

git clone https://github.com/shopsys/project-base.git
git checkout %1$s

# in composer.json, change a version of all shopsys/* packages from "%2$s" to "dev-%1$s as %2$s"

# remove all docker containers
docker rm $(docker ps -a -q)

# remove all docker images
docker rmi --force $(docker images -q)

# install the application following the corresponding installation guide

# run the test suites including acceptance tests:
docker compose exec php-fpm php phing tests tests-acceptance',
            $branchName,
            $version->getVersionString(),
        ));

        $this->confirm('Confirm all tests passed from project-base');

        parent::work($version);
    }
}
