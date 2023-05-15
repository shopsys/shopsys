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
    private bool $isSuccessful = false;

    /**
     * @param \Shopsys\Releaser\FilesProvider\ComposerJsonFilesProvider $composerJsonFilesProvider
     * @param \Symplify\ComposerJsonManipulator\FileSystem\JsonFileManager $jsonFileManager
     * @param \Symplify\MonorepoBuilder\Package\PackageNamesProvider $packageNamesProvider
     */
    public function __construct(
        private readonly ComposerJsonFilesProvider $composerJsonFilesProvider,
        private readonly JsonFileManager $jsonFileManager,
        private readonly PackageNamesProvider $packageNamesProvider,
    ) {
    }

    /**
     * @param \PharIo\Version\Version $version
     * @param string $initialBranchName
     * @return string
     */
    public function getDescription(Version $version, string $initialBranchName = AbstractShopsysReleaseWorker::MAIN_BRANCH_NAME): string
    {
        return 'Validate "require" and "require-dev" version format for all packages';
    }

    /**
     * @param \PharIo\Version\Version $version
     * @param string $initialBranchName
     */
    public function work(Version $version, string $initialBranchName = AbstractShopsysReleaseWorker::MAIN_BRANCH_NAME): void
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
                $version,
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
        return in_array($packageName, $this->packageNamesProvider->provide(), true);
    }

    /**
     * @return string
     */
    public function getStage(): string
    {
        return Stage::RELEASE_CANDIDATE;
    }
}
