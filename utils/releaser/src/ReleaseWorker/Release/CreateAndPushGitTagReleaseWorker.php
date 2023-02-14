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
    public function getDescription(Version $version, string $initialBranchName = AbstractShopsysReleaseWorker::MAIN_BRANCH_NAME): string
    {
        return 'Create, [Manually] push a git tag, and [Manually - if not on master branch] split monorepo';
    }

    /**
     * @param \PharIo\Version\Version $version
     * @param string $initialBranchName
     */
    public function work(Version $version, string $initialBranchName = AbstractShopsysReleaseWorker::MAIN_BRANCH_NAME): void
    {
        $versionString = $version->getOriginalString();
        $this->processRunner->run('git tag ' . $versionString);
        $this->symfonyStyle->note(
            sprintf('You need to push tag manually using "git push origin %s" command.', $versionString)
        );

        $this->confirm(sprintf('Confirm that tag "%s" is pushed', $versionString));
        if ($this->currentBranchName === AbstractShopsysReleaseWorker::MAIN_BRANCH_NAME) {
            $this->symfonyStyle->note(
                'Rest assured, after you push the tagged master branch, the new tag will be propagated to packagist once the project is built and split on Heimdall automatically.'
            );
        } else {
            $this->symfonyStyle->note(
                sprintf(
                    'After you push the tag, you need use to split the "%s" branch using "tool-monorepo-split" on Heimdall (http://heimdall:8080/view/Tools/job/tool-monorepo-split/)',
                    $this->currentBranchName
                )
            );
            $this->symfonyStyle->note(
                'Rest assured, after you split the branch, the new tag will be propagated to packagist automatically.'
            );
            $this->confirm('Confirm the branch is split.');
        }
    }

    /**
     * @return string
     */
    public function getStage(): string
    {
        return Stage::RELEASE;
    }
}
