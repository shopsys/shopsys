<?php

declare(strict_types=1);

namespace Shopsys\Releaser\ReleaseWorker\AfterRelease;

use PharIo\Version\Version;
use Shopsys\Releaser\ReleaseWorker\AbstractShopsysReleaseWorker;
use Shopsys\Releaser\Stage;

final class EnsureRoadmapIsUpdatedReleaseWorker extends AbstractShopsysReleaseWorker
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
        return '[Manually] Ensure "Roadmap" is updated';
    }

    /**
     * @param \PharIo\Version\Version $version
     * @param string $initialBranchName
     */
    public function work(
        Version $version,
        string $initialBranchName = AbstractShopsysReleaseWorker::MAIN_BRANCH_NAME,
    ): void {
        $this->symfonyStyle->note([
            'Notify Product owner of the necessity to update the "Roadmap" page on the website.',
            'If the roadmap cannot be updated right now, the user story should be created and put into the current sprint.',
            'The "Roadmap" page is located at https://www.shopsys.cz/product-roadmap/',
        ]);

        $this->confirm('Confirm the roadmap is updated or the user story is in the current sprint.');
    }

    /**
     * @return string[]
     */
    protected function getAllowedStages(): array
    {
        return [Stage::AFTER_RELEASE];
    }
}
