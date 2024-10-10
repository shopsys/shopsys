<?php

declare(strict_types=1);

namespace Shopsys\Releaser\ReleaseWorker;

use PharIo\Version\Version;
use Shopsys\Releaser\FileManipulator\ComposerJsonFileManipulator;
use Shopsys\Releaser\FilesProvider\ComposerJsonFilesProvider;
use Shopsys\Releaser\FilesProvider\PackageNamesProvider;

abstract class AbstractSetMutualDependenciesToVersionReleaseWorker extends AbstractShopsysReleaseWorker
{
    /**
     * @param \Shopsys\Releaser\FilesProvider\ComposerJsonFilesProvider $composerJsonFilesProvider
     * @param \Shopsys\Releaser\FileManipulator\ComposerJsonFileManipulator $composerJsonFileManipulator
     * @param \Shopsys\Releaser\FilesProvider\PackageNamesProvider $packageNamesProvider
     */
    public function __construct(
        protected readonly ComposerJsonFilesProvider $composerJsonFilesProvider,
        protected readonly ComposerJsonFileManipulator $composerJsonFileManipulator,
        protected readonly PackageNamesProvider $packageNamesProvider,
    ) {
    }

    /**
     * @param \PharIo\Version\Version $version
     * @return string
     */
    abstract protected function getVersionString(Version $version): string;

    /**
     * @param \PharIo\Version\Version $version
     * @param string $initialBranchName
     * @return string
     */
    public function getDescription(
        Version $version,
        string $initialBranchName = AbstractShopsysReleaseWorker::MAIN_BRANCH_NAME,
    ): string {
        return sprintf('Set mutual package dependencies to "%s" version', $this->getVersionString($version));
    }

    /**
     * @param \PharIo\Version\Version $version
     * @param string $initialBranchName
     */
    public function work(
        Version $version,
        string $initialBranchName = AbstractShopsysReleaseWorker::MAIN_BRANCH_NAME,
    ): void {
        $this->composerJsonFileManipulator->setMutualDependenciesToVersion(
            $this->composerJsonFilesProvider->provideExcludingMonorepoComposerJson(),
            $this->packageNamesProvider->provide(),
            $this->getVersionString($version),
        );

        $this->commit(sprintf(
            'all Shopsys packages are now dependent on "%s" version of all other Shopsys packages',
            $this->getVersionString($version),
        ));

        $this->success();
    }
}
