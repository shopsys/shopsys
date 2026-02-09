<?php

declare(strict_types=1);

namespace Shopsys\Releaser\ReleaseWorker\AfterRelease;

use Override;
use PharIo\Version\Version;
use Shopsys\Releaser\FileManipulator\ComposerJsonFileManipulator;
use Shopsys\Releaser\FilesProvider\ComposerJsonFilesProvider;
use Shopsys\Releaser\FilesProvider\PackageNamesProvider;
use Shopsys\Releaser\ReleaseWorker\AbstractShopsysReleaseWorker;
use Shopsys\Releaser\Stage;

final class SetMutualDependenciesToDevelopmentVersionReleaseWorker extends AbstractShopsysReleaseWorker
{
    public function __construct(
        private readonly ComposerJsonFilesProvider $composerJsonFilesProvider,
        private readonly ComposerJsonFileManipulator $composerJsonFileManipulator,
        private readonly PackageNamesProvider $packageNamesProvider,
    ) {
    }

    #[Override]
    public function getDescription(
        Version $version,
        string $initialBranchName = AbstractShopsysReleaseWorker::MAIN_BRANCH_NAME,
    ): string {
        return sprintf(
            'Set mutual package dependencies to "%s" version',
            $this->getDevelopmentVersionString($version),
        );
    }

    #[Override]
    public function work(
        Version $version,
        string $initialBranchName = AbstractShopsysReleaseWorker::MAIN_BRANCH_NAME,
    ): void {
        $developmentVersion = $this->getDevelopmentVersionString($version);
        $this->composerJsonFileManipulator->setMutualDependenciesToVersion(
            $this->composerJsonFilesProvider->provideExcludingMonorepoComposerJson(),
            $this->packageNamesProvider->provide(),
            $developmentVersion,
        );

        $this->commit(
            sprintf(
                'composer.json in all packages now require other shopsys packages in "%s" version',
                $developmentVersion,
            ),
        );
        $this->confirm(
            sprintf('Confirm you have pushed the new commit into the "%s" branch', $this->currentBranchName),
        );
    }

    /**
     * @return string[]
     */
    #[Override]
    protected function getAllowedStages(): array
    {
        return [Stage::AFTER_RELEASE];
    }

    /**
     * Return new development version (e.g. from 7.3.1 to 7.3.x-dev)
     */
    private function getDevelopmentVersionString(Version $version): string
    {
        return $version->getMajor()->getValue() . '.' . $version->getMinor()->getValue() . '.x-dev';
    }
}
