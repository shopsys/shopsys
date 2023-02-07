<?php

declare(strict_types=1);

namespace Shopsys\Releaser\ReleaseWorker\ReleaseCandidate;

use PharIo\Version\Version;
use Shopsys\Releaser\ReleaseWorker\AbstractShopsysReleaseWorker;
use Shopsys\Releaser\Stage;

final class CheckLatestVersionOfReleaserReleaseWorker extends AbstractShopsysReleaseWorker
{
    /**
     * @param \PharIo\Version\Version $version
     * @return string
     */
    public function getDescription(Version $version): string
    {
        return '[Manually] Check that you are using latest version of Releaser.';
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
     */
    public function work(Version $version): void
    {
        $this->symfonyStyle->note(
            'It is possible, that in current master branch there is improved version of Releaser.
            Check that and update releaser in version branch by replacing Releaser directory with one from master branch.'
        );

        $this->symfonyStyle->warning(
            'Make sure, that you have checked \Shopsys\Releaser\ReleaseWorker\AbstractShopsysReleaseWorker::EXCLUDED_PACKAGES constant, after updating releaser.
            It should contain all packages that are no currently maintained packages by monorepo.'
        );

        $this->confirm('Confirm that there is no newer version of Releaser or that you updated Releaser in version branch.');
    }
}
