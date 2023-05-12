<?php

declare(strict_types=1);

namespace Shopsys\Releaser\ReleaseWorker\AfterRelease;

use PharIo\Version\Version;
use Shopsys\Releaser\FilesProvider\ComposerJsonFilesProvider;
use Shopsys\Releaser\ReleaseWorker\AbstractShopsysReleaseWorker;
use Shopsys\Releaser\Stage;
use Symplify\MonorepoBuilder\DependencyUpdater;
use Symplify\MonorepoBuilder\Package\PackageNamesProvider;

final class SetMutualDependenciesToDevelopmentVersionReleaseWorker extends AbstractShopsysReleaseWorker
{
    /**
     * @param \Shopsys\Releaser\FilesProvider\ComposerJsonFilesProvider $composerJsonFilesProvider
     * @param \Symplify\MonorepoBuilder\DependencyUpdater $dependencyUpdater
     * @param \Symplify\MonorepoBuilder\Package\PackageNamesProvider $packageNamesProvider
     */
    public function __construct(private readonly ComposerJsonFilesProvider $composerJsonFilesProvider, private readonly DependencyUpdater $dependencyUpdater, private readonly PackageNamesProvider $packageNamesProvider)
    {
    }

    /**
     * @param \PharIo\Version\Version $version
     * @param string $initialBranchName
     * @return string
     */
    public function getDescription(Version $version, string $initialBranchName = AbstractShopsysReleaseWorker::MAIN_BRANCH_NAME): string
    {
        return sprintf(
            'Set mutual package dependencies to "%s" version',
            $this->getDevelopmentVersionString($version)
        );
    }

    /**
     * @param \PharIo\Version\Version $version
     * @param string $initialBranchName
     */
    public function work(Version $version, string $initialBranchName = AbstractShopsysReleaseWorker::MAIN_BRANCH_NAME): void
    {
        $developmentVersion = $this->getDevelopmentVersionString($version);
        $this->dependencyUpdater->updateFileInfosWithPackagesAndVersion(
            $this->composerJsonFilesProvider->provideExcludingMonorepoComposerJson(),
            $this->packageNamesProvider->provide(),
            $developmentVersion
        );

        $this->commit(
            sprintf(
                'composer.json in all packages now require other shopsys packages in "%s" version',
                $developmentVersion
            )
        );
        $this->confirm(
            sprintf('Confirm you have pushed the new commit into the "%s" branch', $this->currentBranchName)
        );

        if ($this->currentBranchName === AbstractShopsysReleaseWorker::MAIN_BRANCH_NAME) {
            return;
        }

        $this->symfonyStyle->note(
            sprintf('You are not on master branch so you have to split "%s" branch using tool-monorepo-force-split-branch manually on Heimdall now.
            You will need the split monorepo later for verifying local intallation.', $this->currentBranchName)
        );
        $this->confirm('Confirm the monorepo split is running.');
    }

    /**
     * @return string
     */
    public function getStage(): string
    {
        return Stage::AFTER_RELEASE;
    }

    /**
     * Return new development version (e.g. from 7.3.1 to 7.3.x-dev)
     *
     * @param \PharIo\Version\Version $version
     * @return string
     */
    private function getDevelopmentVersionString(Version $version): string
    {
        return $version->getMajor()->getValue() . '.' . $version->getMinor()->getValue() . '.x-dev';
    }
}
