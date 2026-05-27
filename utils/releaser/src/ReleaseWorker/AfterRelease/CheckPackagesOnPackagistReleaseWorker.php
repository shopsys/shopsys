<?php

declare(strict_types=1);

namespace Shopsys\Releaser\ReleaseWorker\AfterRelease;

use Override;
use PharIo\Version\Version;
use Shopsys\Releaser\Packagist\PackageProvider;
use Shopsys\Releaser\ReleaseWorker\AbstractShopsysReleaseWorker;
use Shopsys\Releaser\Stage;
use Shopsys\Releaser\Wait\AllPackagistVersionsAvailable;

final class CheckPackagesOnPackagistReleaseWorker extends AbstractShopsysReleaseWorker
{
    public function __construct(private readonly PackageProvider $packageProvider)
    {
    }

    /**
     * @return string[]
     */
    #[Override]
    protected function getAllowedStages(): array
    {
        return [Stage::AFTER_RELEASE];
    }

    #[Override]
    public function getDescription(
        Version $version,
        string $initialBranchName = AbstractShopsysReleaseWorker::MAIN_BRANCH_NAME,
    ): string {
        return 'Check there are new versions of all packages on packagist';
    }

    #[Override]
    public function work(
        Version $version,
        string $initialBranchName = AbstractShopsysReleaseWorker::MAIN_BRANCH_NAME,
    ): void {
        $packagesWithVersions = $this->packageProvider->getPackagesWithVersionsByOrganization('shopsys', self::EXCLUDED_PACKAGES);

        $packageWithoutVersion = [];
        $versionsAsString = $version->getOriginalString();

        foreach ($packagesWithVersions as $package => $packageVersions) {
            if (in_array($versionsAsString, $packageVersions, true)) {
                continue;
            }

            $packageWithoutVersion[] = $package;
        }

        if ($packageWithoutVersion === []) {
            $this->success();

            return;
        }

        $this->symfonyStyle->warning(
            sprintf('Some packages on packagist do not yet have "%s" version', $versionsAsString),
        );
        $this->symfonyStyle->listing($packageWithoutVersion);

        $this->waitFor(new AllPackagistVersionsAvailable(
            $this->packageProvider,
            $packageWithoutVersion,
            $versionsAsString,
        ));

        $this->success();
    }
}
