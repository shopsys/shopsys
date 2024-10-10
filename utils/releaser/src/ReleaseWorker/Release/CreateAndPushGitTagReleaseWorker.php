<?php

declare(strict_types=1);

namespace Shopsys\Releaser\ReleaseWorker\Release;

use PharIo\Version\Version;
use Shopsys\Releaser\ReleaseWorker\AbstractShopsysReleaseWorker;
use Shopsys\Releaser\Stage;

final class CreateAndPushGitTagReleaseWorker extends AbstractShopsysReleaseWorker
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
        return 'Create and [Manually] push a git tag';
    }

    /**
     * @param \PharIo\Version\Version $version
     * @param string $initialBranchName
     */
    public function work(
        Version $version,
        string $initialBranchName = AbstractShopsysReleaseWorker::MAIN_BRANCH_NAME,
    ): void {
        $tag = $version->getOriginalString();
        $this->processRunner->run('git tag ' . $tag);
        $this->symfonyStyle->note(
            sprintf('You need to push tag manually using "git push origin %s" command.', $tag),
        );

        $this->confirm(sprintf('Confirm that tag "%s" is pushed', $tag));

        $this->symfonyStyle->note('You need to push the branch manually using "git push origin" command.');

        $this->confirm(sprintf('Confirm that the branch "%s" is pushed', $initialBranchName));

        $this->symfonyStyle->note(
            'Rest assured, after you push the branch, the new tag will be propagated to packagist after the branch is split automatically on GitHub Actions.',
        );
    }

    /**
     * @return string[]
     */
    protected function getAllowedStages(): array
    {
        return [Stage::RELEASE];
    }
}
