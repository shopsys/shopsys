<?php

declare(strict_types=1);

namespace Shopsys\Releaser\ReleaseWorker\ReleaseCandidate;

use PharIo\Version\Version;
use Shopsys\Releaser\ReleaseWorker\AbstractShopsysReleaseWorker;
use Shopsys\Releaser\Stage;

final class StopMergingReleaseWorker extends AbstractShopsysReleaseWorker
{
    /**
     * @param \PharIo\Version\Version $version
     * @return string
     */
    public function getDescription(Version $version): string
    {
        return sprintf('[Manually] Tell team to stop merging to "%s" branch', $this->currentBranchName);
    }

    /**
     * @param \PharIo\Version\Version $version
     * @param string $initialBranchName
     */
    public function work(Version $version, string $initialBranchName = 'master'): void
    {
        $this->symfonyStyle->note(
            sprintf(
                'You need to write a warning message into "team_ssfw_devs" slack channel, as well as mark the "merge" column on the whiteboard in the office with a significant red cross along with "release in progress, do not merge to "%s" branch" note.',
                $this->currentBranchName
            )
        );
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
