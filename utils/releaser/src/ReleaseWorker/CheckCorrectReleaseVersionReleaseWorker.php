<?php

declare(strict_types=1);

namespace Shopsys\Releaser\ReleaseWorker;

use PharIo\Version\Version;
use RuntimeException;
use Shopsys\Releaser\Stage;

final class CheckCorrectReleaseVersionReleaseWorker extends AbstractShopsysReleaseWorker
{
    /**
     * @param \PharIo\Version\Version $version
     * @return string
     */
    public function getDescription(Version $version): string
    {
        return 'Check that correct version string has been entered.';
    }

    /**
     * @return string
     */
    public function getStage(): string
    {
        return Stage::RELEASE_CANDIDATE;
    }

    /**
     * @param \PharIo\Version\Version $version
     * @param string $initialBranchName
     */
    public function work(Version $version, string $initialBranchName = 'master'): void
    {
        if (!str_starts_with($version->getOriginalString(), 'v')) {
            throw new RuntimeException(
                'Name of released version must start with \'v\''
            );
        }

        if ($version->getOriginalString() !== 'v' . $version->getVersionString()) {
            throw new RuntimeException(
                'Version string needs to follow SemVer format (e.g. v11.0.0)'
            );
        }
    }
}
