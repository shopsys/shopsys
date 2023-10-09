<?php

declare(strict_types=1);

namespace Shopsys\Releaser\ReleaseWorker\ReleaseCandidate;

use PharIo\Version\Version;
use Shopsys\Releaser\ReleaseWorker\AbstractShopsysReleaseWorker;
use Shopsys\Releaser\Stage;

final class UpdateChangelogReleaseWorker extends AbstractShopsysReleaseWorker
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
        return 'Dump new features to appropriate CHANGELOG-XX.X.md, save new release as draft and [Manually] check everything is ok';
    }

    /**
     * @param \PharIo\Version\Version $version
     * @param string $initialBranchName
     */
    public function work(
        Version $version,
        string $initialBranchName = AbstractShopsysReleaseWorker::MAIN_BRANCH_NAME,
    ): void {
        $this->symfonyStyle->note(
            sprintf(
                'In order to generate new changelog entries you need to go to https://github.com/shopsys/shopsys/releases/new?tag=%s&target=%s&title=%s',
                $version->getOriginalString(),
                $this->currentBranchName,
                $version->getOriginalString() . ' - ' . date('Y-m-d'),
            ),
        );

        $this->symfonyStyle->note('Choose previous highest tag as Previous tag and then click on Generate release notes.');

        $this->symfonyStyle->note('Copy contents of release to appropriate CHANGELOG-XX.X.md with appropriate title and correct formatting.');

        $this->symfonyStyle->note(
            sprintf(
                'Save release as draft and commit new changelog content with message "changelog is now updated for %s release"',
                $version->getOriginalString(),
            ),
        );

        $this->confirm('Confirm you have checked appropriate CHANGELOG-XX.X.md and the changes are committed. Also confirm that release is saved as draft.');
    }

    /**
     * @return string
     */
    public function getStage(): string
    {
        return Stage::RELEASE_CANDIDATE;
    }
}
