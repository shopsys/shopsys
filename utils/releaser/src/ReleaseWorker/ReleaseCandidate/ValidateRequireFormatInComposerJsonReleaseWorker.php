<?php

declare(strict_types=1);

namespace Shopsys\Releaser\ReleaseWorker\ReleaseCandidate;

use Nette\Utils\Strings;
use PharIo\Version\Version;
use Shopsys\Releaser\FilesProvider\ComposerJsonFilesProvider;
use Shopsys\Releaser\ReleaseWorker\AbstractShopsysReleaseWorker;
use Shopsys\Releaser\ReleaseWorker\Message;
use Shopsys\Releaser\Stage;
use Symfony\Component\Finder\SplFileInfo;
use Symplify\ComposerJsonManipulator\FileSystem\JsonFileManager;
use Symplify\MonorepoBuilder\Package\PackageNamesProvider;

final class ValidateRequireFormatInComposerJsonReleaseWorker extends AbstractShopsysReleaseWorker
{
    /**
     * @var \Shopsys\Releaser\FilesProvider\ComposerJsonFilesProvider
     */
    private $composerJsonFilesProvider;

    /**
     * @var \Symplify\ComposerJsonManipulator\FileSystem\JsonFileManager
     */
    private $jsonFileManager;

    /**
     * @var bool
     */
    private $isSuccessful = false;

    /**
     * @var \Symplify\MonorepoBuilder\Package\PackageNamesProvider
     */
    private $packageNamesProvider;

    /**
     * @param \Shopsys\Releaser\FilesProvider\ComposerJsonFilesProvider $composerJsonFilesProvider
     * @param \Symplify\ComposerJsonManipulator\FileSystem\JsonFileManager $jsonFileManager
     * @param \Symplify\MonorepoBuilder\Package\PackageNamesProvider $packageNamesProvider
     */
    public function __construct(
        ComposerJsonFilesProvider $composerJsonFilesProvider,
        JsonFileManager $jsonFileManager,
        PackageNamesProvider $packageNamesProvider
    ) {
        $this->composerJsonFilesProvider = $composerJsonFilesProvider;
        $this->jsonFileManager = $jsonFileManager;
        $this->packageNamesProvider = $packageNamesProvider;
    }

    /**
     * @param \PharIo\Version\Version $version
     * @return string
     */
    public function getDescription(Version $version): string
    {
        return 'Validate "require" and "require-dev" version format for all packages';
    }

    /**
     * Higher first
     * @return int
     */
    public function getPriority(): int
    {
        return 920;
    }

    /**
     * @param \PharIo\Version\Version $version
     */
    public function work(Version $version): void
    {
        foreach ($this->composerJsonFilesProvider->provideAll() as $smartFileInfo) {
            $jsonContent = $this->jsonFileManager->loadFromFileInfo($smartFileInfo);

            $this->validateVersions($jsonContent, 'require', $smartFileInfo);
            $this->validateVersions($jsonContent, 'require-dev', $smartFileInfo);
        }

        if ($this->isSuccessful) {
            $this->symfonyStyle->success(Message::SUCCESS);
        } else {
            $this->confirm('Confirm all the requires are in the valid format');
        }
    }

    /**
     * @param mixed[] $jsonContent
     * @param string $section
     * @param \Symfony\Component\Finder\SplFileInfo $splFileInfo
     */
    private function validateVersions(array $jsonContent, string $section, SplFileInfo $splFileInfo): void
    {
        if (!isset($jsonContent[$section])) {
            return;
        }

        foreach ($jsonContent[$section] as $packageName => $version) {
            if ($this->shouldSkipPackageNameAndVersion($packageName, $version)) {
                continue;
            }

            $this->symfonyStyle->warning(sprintf(
                '"%s" file has invalid version format for "%s": "%s"',
                $splFileInfo->getPathname(),
                $packageName,
                $version
            ));

            $this->isSuccessful = false;
        }
    }

    /**
     * @param string $packageName
     * @param string $version
     * @return bool
     */
    private function shouldSkipPackageNameAndVersion(string $packageName, string $version): bool
    {
        if (Strings::startsWith($packageName, 'ext-')) {
            return true;
        }

        if (Strings::startsWith($version, '^')) {
            return true;
        }

        // skip shopsys packages mutual dependencies in monorepo
        if (in_array($packageName, $this->packageNamesProvider->provide(), true)) {
            return true;
        }

        return false;
    }

    /**
     * @return string
     */
    public function getStage(): string
    {
        return Stage::RELEASE_CANDIDATE;
    }
}
