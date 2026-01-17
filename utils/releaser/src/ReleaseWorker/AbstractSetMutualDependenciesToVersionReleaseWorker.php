<?php

declare(strict_types=1);

namespace Shopsys\Releaser\ReleaseWorker;

use Override;
use PharIo\Version\Version;
use Shopsys\Releaser\FileManipulator\ComposerJsonFileManipulator;
use Shopsys\Releaser\FilesProvider\ComposerJsonFilesProvider;
use Shopsys\Releaser\FilesProvider\PackageNamesProvider;

abstract class AbstractSetMutualDependenciesToVersionReleaseWorker extends AbstractShopsysReleaseWorker
{
    public function __construct(
        protected readonly ComposerJsonFilesProvider $composerJsonFilesProvider,
        protected readonly ComposerJsonFileManipulator $composerJsonFileManipulator,
        protected readonly PackageNamesProvider $packageNamesProvider,
    ) {
    }

    abstract protected function getVersionString(Version $version): string;

    #[Override]
    public function getDescription(
        Version $version,
        string $initialBranchName = AbstractShopsysReleaseWorker::MAIN_BRANCH_NAME,
    ): string {
        return sprintf('Set mutual package dependencies to "%s" version', $this->getVersionString($version));
    }

    #[Override]
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
