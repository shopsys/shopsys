<?php

declare(strict_types=1);

namespace Shopsys\Releaser\ReleaseWorker\AfterRelease;

use Override;
use PharIo\Version\Version;
use Shopsys\Releaser\ReleaseWorker\AbstractShopsysReleaseWorker;
use Shopsys\Releaser\Stage;

final class PostInfoToSlackReleaseWorker extends AbstractShopsysReleaseWorker
{
    #[Override]
    public function getDescription(
        Version $version,
        string $initialBranchName = AbstractShopsysReleaseWorker::MAIN_BRANCH_NAME,
    ): string {
        return '[Manually] Post info to slack channels';
    }

    #[Override]
    public function work(
        Version $version,
        string $initialBranchName = AbstractShopsysReleaseWorker::MAIN_BRANCH_NAME,
    ): void {
        $this->symfonyStyle->note(
            'Add new posts to these channels: #news in public Slack, #group_ssfw in internal Slack. You do not need to write essays, just point out one or two most interesting changes and add links to the "release highlights" article and release notes notes on Github.',
        );
        $this->confirm('Confirm the Slack is noted');
    }

    /**
     * @return string[]
     */
    #[Override]
    protected function getAllowedStages(): array
    {
        return [Stage::AFTER_RELEASE];
    }
}
