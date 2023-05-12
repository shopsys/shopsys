<?php

declare(strict_types=1);

namespace Shopsys\Releaser\ReleaseWorker\ReleaseCandidate;

use Nette\Utils\FileSystem;
use PharIo\Version\Version;
use Shopsys\Releaser\FileManipulator\FrameworkBundleVersionFileManipulator;
use Shopsys\Releaser\ReleaseWorker\AbstractShopsysReleaseWorker;
use Shopsys\Releaser\ReleaseWorker\Message;
use Shopsys\Releaser\Stage;
use Symplify\SmartFileSystem\SmartFileInfo;

final class SetFrameworkBundleVersionReleaseWorker extends AbstractShopsysReleaseWorker
{
    /**
     * @param \Shopsys\Releaser\FileManipulator\FrameworkBundleVersionFileManipulator $frameworkBundleVersionFileManipulator
     */
    public function __construct(
        private readonly FrameworkBundleVersionFileManipulator $frameworkBundleVersionFileManipulator
    ) {
    }

    /**
     * @param \PharIo\Version\Version $version
     * @param string $initialBranchName
     * @return string
     */
    public function getDescription(Version $version, string $initialBranchName = AbstractShopsysReleaseWorker::MAIN_BRANCH_NAME): string
    {
        return 'Set ShopsysFrameworkBundle version to released version and commit it.';
    }

    /**
     * @param \PharIo\Version\Version $version
     * @param string $initialBranchName
     */
    public function work(Version $version, string $initialBranchName = AbstractShopsysReleaseWorker::MAIN_BRANCH_NAME): void
    {
        $this->updateFrameworkBundleVersion($version);

        $this->commit(sprintf(
            'ShopsysFrameworkBundle: version updated to "%s"',
            $version->getVersionString()
        ));

        $this->symfonyStyle->success(Message::SUCCESS);
    }

    /**
     * @return string
     */
    public function getStage(): string
    {
        return Stage::RELEASE_CANDIDATE;
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
            $version
        );

        FileSystem::write($upgradeFilePath, $newUpgradeContent);
    }
}
