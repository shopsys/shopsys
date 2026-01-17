<?php

declare(strict_types=1);

namespace Shopsys\Releaser\ReleaseWorker\AfterRelease;

use Nette\Utils\FileSystem;
use Override;
use PharIo\Version\Version;
use Shopsys\Releaser\FileManipulator\FrameworkBundleVersionFileManipulator;
use Shopsys\Releaser\ReleaseWorker\AbstractShopsysReleaseWorker;
use Shopsys\Releaser\Stage;

final class SetFrameworkBundleVersionToDevReleaseWorker extends AbstractShopsysReleaseWorker
{
    public function __construct(
        private readonly FrameworkBundleVersionFileManipulator $frameworkBundleVersionFileManipulator,
    ) {
    }

    #[Override]
    public function getDescription(
        Version $version,
        string $initialBranchName = AbstractShopsysReleaseWorker::MAIN_BRANCH_NAME,
    ): string {
        return 'Set ShopsysFrameworkBundle version to next dev version and commit it.';
    }

    #[Override]
    public function work(
        Version $version,
        string $initialBranchName = AbstractShopsysReleaseWorker::MAIN_BRANCH_NAME,
    ): void {
        $developmentVersion = $this->askForNextDevelopmentVersion($version, true);
        $this->updateFrameworkBundleVersion($developmentVersion);

        $this->commit(sprintf(
            'ShopsysFrameworkBundle: version updated to "%s"',
            $developmentVersion->getVersionString(),
        ));

        $this->symfonyStyle->note(sprintf('You need to push the "%s" branch manually', $this->currentBranchName));
        $this->confirm(sprintf('Confirm you have pushed the "%s "branch.', $this->currentBranchName));
    }

    /**
     * @return string[]
     */
    #[Override]
    protected function getAllowedStages(): array
    {
        return [Stage::AFTER_RELEASE];
    }

    private function updateFrameworkBundleVersion(Version $version): void
    {
        $upgradeFilePath = getcwd() . FrameworkBundleVersionFileManipulator::FRAMEWORK_BUNDLE_VERSION_FILE_PATH;
        $upgradeFileContent = FileSystem::read($upgradeFilePath);

        $newUpgradeContent = $this->frameworkBundleVersionFileManipulator->updateFrameworkBundleVersion(
            $upgradeFileContent,
            $version,
        );

        FileSystem::write($upgradeFilePath, $newUpgradeContent);
    }
}
