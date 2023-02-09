<?php

declare(strict_types=1);

namespace Shopsys\Releaser\ReleaseWorker\AfterRelease;

use PharIo\Version\Version;
use Shopsys\Releaser\ReleaseWorker\AbstractCheckShopsysInstallReleaseWorker;
use Shopsys\Releaser\Stage;

final class CheckShopsysInstallReleaseWorker extends AbstractCheckShopsysInstallReleaseWorker
{
    /**
     * @param \PharIo\Version\Version $version
     * @param string $initialBranchName
     * @return string
     */
    public function getDescription(Version $version, string $initialBranchName = 'master'): string
    {
        return '[Manually] Install Shopsys Framework (project-base) using installation guides on all supported operating systems. You need to wait with the installation until the monorepo is split.';
    }

    /**
     * @return string
     */
    public function getStage(): string
    {
        return Stage::AFTER_RELEASE;
    }

    /**
     * @param \PharIo\Version\Version $version
     * @param string $initialBranchName
     */
    public function work(Version $version, string $initialBranchName = 'master'): void
    {
        $this->symfonyStyle->note(sprintf(
            'Instructions for installation:

git clone https://github.com/shopsys/project-base.git
git checkout %1$s

# remove all docker containers
docker rm $(docker ps -a -q)

# remove all docker images
docker rmi --force $(docker images -q)

# install the application following the corresponding installation guide',
            $version->getVersionString()
        ));

        parent::work($version);
    }
}
