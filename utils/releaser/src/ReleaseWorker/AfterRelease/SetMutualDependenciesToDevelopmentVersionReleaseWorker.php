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
     * @var \Shopsys\Releaser\FilesProvider\ComposerJsonFilesProvider
     */
    private $composerJsonFilesProvider;

    /**
     * @var \Symplify\MonorepoBuilder\DependencyUpdater
     */
    private $dependencyUpdater;

    /**
     * @var \Symplify\MonorepoBuilder\Package\PackageNamesProvider
     */
    private $packageNamesProvider;

    /**
     * @param \Shopsys\Releaser\FilesProvider\ComposerJsonFilesProvider $composerJsonFilesProvider
     * @param \Symplify\MonorepoBuilder\DependencyUpdater $dependencyUpdater
     * @param \Symplify\MonorepoBuilder\Package\PackageNamesProvider $packageNamesProvider
     */
    public function __construct(ComposerJsonFilesProvider $composerJsonFilesProvider, DependencyUpdater $dependencyUpdater, PackageNamesProvider $packageNamesProvider)
    {
        $this->composerJsonFilesProvider = $composerJsonFilesProvider;
        $this->dependencyUpdater = $dependencyUpdater;
        $this->packageNamesProvider = $packageNamesProvider;
    }

    /**
     * @param \PharIo\Version\Version $version
     * @return string
     */
    public function getDescription(Version $version): string
    {
        return sprintf('Set mutual package dependencies to "%s" version', $this->getDevelopmentVersionString($version));
    }

    /**
     * Higher first
     * @return int
     */
    public function getPriority(): int
    {
        return 160;
    }

    /**
     * @param \PharIo\Version\Version $version
     */
    public function work(Version $version): void
    {
        $developmentVersion = $this->getDevelopmentVersionString($version);
        $this->dependencyUpdater->updateFileInfosWithPackagesAndVersion(
            $this->composerJsonFilesProvider->provideExcludingMonorepoComposerJson(),
            $this->packageNamesProvider->provide(),
            $developmentVersion
        );

        $this->commit(sprintf('composer.json in all packages now require other shopsys packages in "%s" version', $developmentVersion));
        $this->confirm(sprintf('Confirm you have pushed the new commit into the "%s" branch', $this->initialBranchName));
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
     * @param \PharIo\Version\Version $version
     * @return string
     */
    private function getDevelopmentVersionString(Version $version): string
    {
        return $version->getMajor()->getValue() . '.' . $version->getMinor()->getValue() . '.x-dev';
    }
}
