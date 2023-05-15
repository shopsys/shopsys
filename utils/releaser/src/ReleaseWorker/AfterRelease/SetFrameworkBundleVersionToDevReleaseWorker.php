<?php

declare(strict_types=1);

namespace Shopsys\Releaser\ReleaseWorker\AfterRelease;

use Nette\Utils\FileSystem;
use PharIo\Version\Version;
use Shopsys\Releaser\FileManipulator\FrameworkBundleVersionFileManipulator;
use Shopsys\Releaser\ReleaseWorker\AbstractShopsysReleaseWorker;
use Shopsys\Releaser\Stage;
use Symplify\SmartFileSystem\SmartFileInfo;

final class SetFrameworkBundleVersionToDevReleaseWorker extends AbstractShopsysReleaseWorker
{
    /**
     * @param \Shopsys\Releaser\FileManipulator\FrameworkBundleVersionFileManipulator $frameworkBundleVersionFileManipulator
     */
    public function __construct(private readonly FrameworkBundleVersionFileManipulator $frameworkBundleVersionFileManipulator)
    {
    }

    /**
     * @param \PharIo\Version\Version $version
     * @param string $initialBranchName
     * @return string
     */
    public function getDescription(Version $version, string $initialBranchName = AbstractShopsysReleaseWorker::MAIN_BRANCH_NAME): string
    {
        return 'Set ShopsysFrameworkBundle version to next dev version and commit it.';
    }

    /**
     * @param \PharIo\Version\Version $version
     * @param string $initialBranchName
     */
    public function work(Version $version, string $initialBranchName = AbstractShopsysReleaseWorker::MAIN_BRANCH_NAME): void
    {
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
     * @return string
     */
    public function getStage(): string
    {
        return Stage::AFTER_RELEASE;
    }

    /**
     * @param \PharIo\Version\Version $version
     */
    private function updateFrameworkBundleVersion(Version $version): void
    {
        $upgradeFilePath = getcwd() . FrameworkBundleVersionFileManipulator::FRAMEWORK_BUNDLE_VERSION_FILE_PATH;
        $upgradeFileInfo = new SmartFileInfo($upgradeFilePath);

        $newUpgradeContent = $this->frameworkBundleVersionFileManipulator->updateFrameworkBundleVersion(
            $upgradeFileInfo,
            $version,
        );

        FileSystem::write($upgradeFilePath, $newUpgradeContent);
    }
}
