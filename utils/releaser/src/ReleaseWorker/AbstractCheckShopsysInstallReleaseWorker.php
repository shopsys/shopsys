<?php

declare(strict_types=1);

namespace Shopsys\Releaser\ReleaseWorker;

use PharIo\Version\Version;

abstract class AbstractCheckShopsysInstallReleaseWorker extends AbstractShopsysReleaseWorker
{
    /**
     * @param \PharIo\Version\Version $version
     * @param string $initialBranchName
     */
    public function work(
        Version $version,
        string $initialBranchName = AbstractShopsysReleaseWorker::MAIN_BRANCH_NAME,
    ): void {
        $this->writeInstructionsForProjectBasePreparation($version);
        $this->writeInstructionsForInstallation();
        $this->confirm('Confirm Shopsys project-base installation works');
    }

    /**
     * @param \PharIo\Version\Version $version
     */
    abstract protected function writeInstructionsForProjectBasePreparation(Version $version): void;

    protected function writeInstructionsForInstallation(): void
    {
        $this->symfonyStyle->note(
            'Instructions for installation:

# remove all docker containers
docker rm $(docker ps -a -q)

# remove all docker images
docker rmi --force $(docker images -q)

# install the application following the corresponding installation guide

# run the test suites including acceptance tests:
docker compose exec php-fpm php phing tests tests-acceptance

# run the cypress tests:
make run-acceptance-tests-actual',
        );
    }
}
