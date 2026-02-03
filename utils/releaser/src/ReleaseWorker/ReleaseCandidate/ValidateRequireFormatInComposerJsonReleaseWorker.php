<?php

declare(strict_types=1);

namespace Shopsys\Releaser\ReleaseWorker\ReleaseCandidate;

use Nette\Utils\Json;
use Override;
use PharIo\Version\Version;
use Shopsys\Releaser\FilesProvider\ComposerJsonFilesProvider;
use Shopsys\Releaser\FilesProvider\PackageNamesProvider;
use Shopsys\Releaser\ReleaseWorker\AbstractShopsysReleaseWorker;
use Shopsys\Releaser\Stage;
use Symfony\Component\Finder\SplFileInfo;

final class ValidateRequireFormatInComposerJsonReleaseWorker extends AbstractShopsysReleaseWorker
{
    private bool $isSuccessful = false;

    public function __construct(
        private readonly ComposerJsonFilesProvider $composerJsonFilesProvider,
        private readonly PackageNamesProvider $packageNamesProvider,
    ) {
    }

    #[Override]
    public function getDescription(
        Version $version,
        string $initialBranchName = AbstractShopsysReleaseWorker::MAIN_BRANCH_NAME,
    ): string {
        return 'Validate "require" and "require-dev" version format for all packages';
    }

    #[Override]
    public function work(
        Version $version,
        string $initialBranchName = AbstractShopsysReleaseWorker::MAIN_BRANCH_NAME,
    ): void {
        foreach ($this->composerJsonFilesProvider->provideAll() as $splFileInfo) {
            $jsonContent = Json::decode($splFileInfo->getContents(), true);

            $this->validateVersions($jsonContent, 'require', $splFileInfo);
            $this->validateVersions($jsonContent, 'require-dev', $splFileInfo);
        }

        if ($this->isSuccessful) {
            $this->success();
        } else {
            $this->confirm('Confirm all the requires are in the valid format');
        }
    }

    /**
     * @param mixed[] $jsonContent
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

    private function shouldSkipPackageNameAndVersion(string $packageName, string $version): bool
    {
        if (str_starts_with($packageName, 'ext-')) {
            return true;
        }

        if (str_starts_with($version, '^')) {
            return true;
        }

        // skip shopsys packages mutual dependencies in monorepo
        return in_array($packageName, $this->packageNamesProvider->provide(), true);
    }

    /**
     * @return string[]
     */
    #[Override]
    protected function getAllowedStages(): array
    {
        return [Stage::RELEASE_CANDIDATE];
    }
}
