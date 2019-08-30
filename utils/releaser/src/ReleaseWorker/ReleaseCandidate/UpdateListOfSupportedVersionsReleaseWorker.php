<?php

declare(strict_types=1);

namespace Shopsys\Releaser\ReleaseWorker\ReleaseCandidate;

use PharIo\Version\Version;
use Shopsys\Releaser\ReleaseWorker\AbstractShopsysReleaseWorker;
use Shopsys\Releaser\Stage;

final class UpdateListOfSupportedVersionsReleaseWorker extends AbstractShopsysReleaseWorker
{
    /**
     * @param \PharIo\Version\Version $version
     * @return string
     */
    public function getDescription(Version $version): string
    {
        return '[Manually] Update the list of currently supported versions mentioned in BC promise (if necessary).';
    }

    /**
     * Higher first
     * @return int
     */
    public function getPriority(): int
    {
        return 843;
    }

    /**
     * @param \PharIo\Version\Version $version
     */
    public function work(Version $version): void
    {
        $this->symfonyStyle->note('When releasing a new MINOR or MAJOR version, make sure the list of currently supported versions in BC promise (https://github.com/shopsys/shopsys/blob/master/docs/contributing/backward-compatibility-promise.md#current-release-plan) is up to date.');
        $this->symfonyStyle->note('If necessary, update the list and commit the change with "backward-compatibility-promise.md: updated list of currently supported versions" commit message.');

        $this->confirm('Confirm the list is up to date');
    }

    /**
     * @return string
     */
    public function getStage(): string
    {
        return Stage::RELEASE_CANDIDATE;
    }
}
