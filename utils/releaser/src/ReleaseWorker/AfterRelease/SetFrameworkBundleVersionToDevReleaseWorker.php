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
     * @var \Shopsys\Releaser\FileManipulator\FrameworkBundleVersionFileManipulator
     */
    private $frameworkBundleVersionFileManipulator;

    /**
     * @param \Shopsys\Releaser\FileManipulator\FrameworkBundleVersionFileManipulator $frameworkBundleVersionFileManipulator
     */
    public function __construct(FrameworkBundleVersionFileManipulator $frameworkBundleVersionFileManipulator)
    {
        $this->frameworkBundleVersionFileManipulator = $frameworkBundleVersionFileManipulator;
    }

    /**
     * @param \PharIo\Version\Version $version
     * @return string
     */
    public function getDescription(Version $version): string
    {
        return 'Set ShopsysFrameworkBundle version to next dev version and commit it.';
    }

    /**
     * Higher first
     * @return int
     */
    public function getPriority(): int
    {
        return 170;
    }

    /**
     * @param \PharIo\Version\Version $version
     */
    public function work(Version $version): void
    {
        $developmentVersion = $this->askForNextDevelopmentVersion($version);
        $this->updateFrameworkBundleVersion($developmentVersion);

        $this->commit(sprintf(
            'ShopsysFrameworkBundle: version updated to "%s"',
            $developmentVersion->getVersionString()
        ));

        $this->symfonyStyle->note(sprintf('You need to push the "%s" branch manually', $this->initialBranchName));
        $this->confirm(sprintf('Confirm you have pushed the "%s "branch.', $this->initialBranchName));
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

        $newUpgradeContent = $this->frameworkBundleVersionFileManipulator->updateFrameworkBundleVersion($upgradeFileInfo, $version);

        FileSystem::write($upgradeFilePath, $newUpgradeContent);
    }
}
