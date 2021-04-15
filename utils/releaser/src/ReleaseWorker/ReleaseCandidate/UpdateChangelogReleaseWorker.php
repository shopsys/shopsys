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
     * @return string
     */
    public function getDescription(Version $version): string
    {
        return 'Dump new features to CHANGELOG.md, clean from placeholders and [Manually] check everything is ok';
    }

    /**
     * Higher first
     * @return int
     */
    public function getPriority(): int
    {
        return 820;
    }

    /**
     * @param \PharIo\Version\Version $version
     */
    public function work(Version $version): void
    {
        $this->symfonyStyle->note('It is necessary to set Github token before the changelog content is generated');
        $githubToken = $this->symfonyStyle->ask('Please enter no-scope Github token (https://github.com/settings/tokens/new)');

        $lastVersionOnCurrentBranch = $this->processRunner->run('git describe --tags --abbrev=0');

        $this->symfonyStyle->note('In order to generate new changelog entries you need to run this command outside of container:');
        $this->symfonyStyle->write(
            sprintf(
                'docker run -it --rm -v "$(pwd)":/usr/local/src/your-app ferrarimarco/github-changelog-generator github_changelog_generator --token %s --release-branch %s --since-tag %s --future-release %s',
                $githubToken,
                $this->initialBranchName,
                trim($lastVersionOnCurrentBranch),
                $version->getVersionString()
            )
        );

        $this->symfonyStyle->note(
            sprintf(
                'You need to review the file, resolve unclassified entries, remove uninteresting entries, and commit the changes manually with "changelog is now updated for %s release"',
                $version->getVersionString()
            )
        );

        $this->confirm('Confirm you have checked CHANGELOG.md and the changes are committed.');
    }

    /**
     * @return string
     */
    public function getStage(): string
    {
        return Stage::RELEASE_CANDIDATE;
    }
}
