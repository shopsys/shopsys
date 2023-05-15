<?php

declare(strict_types=1);

namespace Shopsys\Releaser\ReleaseWorker\AfterRelease;

use PharIo\Version\Version;
use Shopsys\Releaser\Packagist\PackageProvider;
use Shopsys\Releaser\ReleaseWorker\AbstractShopsysReleaseWorker;
use Shopsys\Releaser\ReleaseWorker\Message;
use Shopsys\Releaser\Stage;

final class CheckPackagesOnPackagistReleaseWorker extends AbstractShopsysReleaseWorker
{
    /**
     * @param \Shopsys\Releaser\Packagist\PackageProvider $packageProvider
     */
    public function __construct(private readonly PackageProvider $packageProvider)
    {
    }

    /**
     * @return string
     */
    public function getStage(): string
    {
        return Stage::AFTER_RELEASE;
    }

    /**
     * @param \PharIo\Version\Version $version
     * @param string $initialBranchName
     * @return string
     */
    public function getDescription(Version $version, string $initialBranchName = AbstractShopsysReleaseWorker::MAIN_BRANCH_NAME): string
    {
        return 'Check there are new versions of all packages on packagist';
    }

    /**
     * @param \PharIo\Version\Version $version
     * @param string $initialBranchName
     */
    public function work(Version $version, string $initialBranchName = AbstractShopsysReleaseWorker::MAIN_BRANCH_NAME): void
    {
        $packagesWithVersions = $this->packageProvider->getPackagesWithVersionsByOrganization('shopsys', self::EXCLUDED_PACKAGES);

        $packageWithoutVersion = [];
        $versionsAsString = $version->getOriginalString();

        foreach ($packagesWithVersions as $package => $packageVersions) {
            if (in_array($versionsAsString, $packageVersions, true)) {
                continue;
            }

            $packageWithoutVersion[] = $package;
        }

        if (count($packageWithoutVersion)) {
            $this->symfonyStyle->error(
                sprintf('Some packages on packagist do not have "%s" version', $versionsAsString),
            );
            $this->symfonyStyle->listing($packageWithoutVersion);

            $this->confirm('Confirm the missing versions are fixed');
        } else {
            $this->symfonyStyle->success(Message::SUCCESS);
        }
    }
}
