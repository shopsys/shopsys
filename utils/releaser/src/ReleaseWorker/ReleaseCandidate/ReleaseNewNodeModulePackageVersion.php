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
     * @return string
     */
    public function getDescription(Version $version): string
    {
        return sprintf('[Manually] Release and set new node module package version to "%s"', $version->getVersionString());
    }

    /**
     * @return int
     */
    public function getPriority(): int
    {
        return 770;
    }

    /**
     * @param \PharIo\Version\Version $version
     */
    public function work(Version $version): void
    {
        $this->symfonyStyle->note(sprintf(
            'Instructions for release new node module:

# set new version attribute in packages/framework/assets/package.json to %s

# go to packages/framework/assets

npm login
# pass your credentials (login, password, email)

npm publish

# set new version attribute to %s in project-base/package.json

# commit the changes manually with "npm package is now updated for %s release
',
            $version->getVersionString(),
            $version->getVersionString(),
            $version->getVersionString()
        ));
        $this->confirm('Confirm the merging is stopped');
    }

    /**
     * @return string
     */
    public function getStage(): string
    {
        return Stage::RELEASE_CANDIDATE;
    }
}
