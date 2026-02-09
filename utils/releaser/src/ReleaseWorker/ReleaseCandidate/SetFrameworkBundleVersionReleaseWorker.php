<?php

declare(strict_types=1);

namespace Shopsys\Releaser\ReleaseWorker\ReleaseCandidate;

use Nette\Utils\FileSystem;
use Override;
use PharIo\Version\Version;
use Shopsys\Releaser\FileManipulator\FrameworkBundleVersionFileManipulator;
use Shopsys\Releaser\ReleaseWorker\AbstractShopsysReleaseWorker;
use Shopsys\Releaser\Stage;

final class SetFrameworkBundleVersionReleaseWorker extends AbstractShopsysReleaseWorker
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
        return 'Set ShopsysFrameworkBundle version to released version and commit it.';
    }

    #[Override]
    public function work(
        Version $version,
        string $initialBranchName = AbstractShopsysReleaseWorker::MAIN_BRANCH_NAME,
    ): void {
        $this->updateFrameworkBundleVersion($version);

        $this->commit(sprintf(
            'ShopsysFrameworkBundle: version updated to "%s"',
            $version->getVersionString(),
        ));

        $this->success();
    }

    /**
     * @return string[]
     */
    #[Override]
    protected function getAllowedStages(): array
    {
        return [Stage::RELEASE_CANDIDATE];
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
