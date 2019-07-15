<?php

declare(strict_types=1);

namespace Shopsys\Releaser\ReleaseWorker\AfterRelease;

use PharIo\Version\Version;
use Shopsys\Releaser\ReleaseWorker\AbstractShopsysReleaseWorker;
use Shopsys\Releaser\Stage;

final class EnableMergingReleaseWorker extends AbstractShopsysReleaseWorker
{
    /**
     * @param \PharIo\Version\Version $version
     * @return string
     */
    public function getDescription(Version $version): string
    {
        return sprintf('[Manually] Enable merging to "%s" branch', $this->initialBranchName);
    }

    /**
     * Higher first
     * @return int
     */
    public function getPriority(): int
    {
        return 145;
    }

    /**
     * @param \PharIo\Version\Version $version
     */
    public function work(Version $version): void
    {
        $this->symfonyStyle->note(sprintf('Enable merging to "%s" - let your colleagues know in "team_ssfw_devs" Slack channel, and erase the red cross from the "merge" column on the whiteboard in the office.', $this->initialBranchName));
        $this->confirm(sprintf('Confirm merging to "%s" is enabled.', $this->initialBranchName));
    }

    /**
     * @return string
     */
    public function getStage(): string
    {
        return Stage::AFTER_RELEASE;
    }
}
