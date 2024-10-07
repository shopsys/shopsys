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
        return sprintf(
            'Dump new features to appropriate CHANGELOG-%s.%s.md, save new release as draft and [Manually] check everything is ok',
            $version->getMajor()->getValue(),
            $version->getMinor()->getValue(),
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
        $this->symfonyStyle->note(
            sprintf(
                'In order to generate new changelog entries you need to go to https://github.com/shopsys/shopsys/releases/new?tag=%s&target=%s&title=%s',
                $version->getOriginalString(),
                $version->getMajor()->getValue() . '.' . $version->getMinor()->getValue(),
                $version->getOriginalString() . ' - ' . date('Y-m-d'),
            ),
        );

        $this->symfonyStyle->note('Choose previous highest tag as Previous tag and then click on Generate release notes.');

        $this->symfonyStyle->note(
            sprintf(
                'Copy contents of release to CHANGELOG-%s.%s.md with appropriate title and check the changes.',
                $version->getMajor()->getValue(),
                $version->getMinor()->getValue(),
            ),
        );

        $this->symfonyStyle->note('Save release as draft');

        $this->confirm(
            sprintf(
                'Confirm you have copied the release notes to CHANGELOG-%s.%s.md, checked the changes and saved release as draft.',
                $version->getMajor()->getValue(),
                $version->getMinor()->getValue(),
            ),
        );

        $this->processRunner->run('php phing markdown-fix');

        $this->commit(sprintf('changelog is now updated for %s release', $version->getOriginalString()));
    }

    /**
     * @return string[]
     */
    protected function getAllowedStages(): array
    {
        return [Stage::RELEASE_CANDIDATE];
    }
}
