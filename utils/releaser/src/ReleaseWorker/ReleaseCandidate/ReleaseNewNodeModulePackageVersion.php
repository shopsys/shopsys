<?php

declare(strict_types=1);

namespace Shopsys\Releaser\ReleaseWorker\ReleaseCandidate;

use PharIo\Version\Version;
use Shopsys\Releaser\ReleaseWorker\AbstractShopsysReleaseWorker;
use Shopsys\Releaser\Stage;

class ReleaseNewNodeModulePackageVersion extends AbstractShopsysReleaseWorker
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
        return sprintf(
            '[Manually] Release and set new node module package version to "%s"',
            $version->getVersionString(),
        );
    }

    /**
     * @param \PharIo\Version\Version $version
     * @param string $initialBranchName
     */
    public function work(
        Version $version,
        string $initialBranchName = AbstractShopsysReleaseWorker::MAIN_BRANCH_NAME,
    ): void {
        $this->symfonyStyle->note(sprintf(
            'Instructions for release new node module:

# set new version attribute in packages/framework/assets/package.json to %s

# go to packages/framework/assets

npm login
# pass your credentials (login, password, email) (these credentials are available in BitWarden)

npm publish

# set new version of @shopsys/framework to %s in project-base/app/package.json
',
            $version->getVersionString(),
            $version->getVersionString(),
        ));
        $this->confirm('Confirm the new version of NPM package is published');
        $this->confirm('Confirm that you have updated version of @shopsys/framework in project-base/app/package.json and the changes are ready to be committed');
        $this->commit(sprintf(
            'npm package is now updated for %s release',
            $version->getVersionString(),
        ));
    }

    /**
     * @return string[]
     */
    protected function getAllowedStages(): array
    {
        return [Stage::RELEASE_CANDIDATE];
    }
}
