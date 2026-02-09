<?php

declare(strict_types=1);

namespace Shopsys\Releaser\ReleaseWorker;

use Override;
use PharIo\Version\Version;
use RuntimeException;
use Shopsys\Releaser\Stage;

final class CheckCorrectReleaseVersionReleaseWorker extends AbstractShopsysReleaseWorker
{
    #[Override]
    public function getDescription(
        Version $version,
        string $initialBranchName = AbstractShopsysReleaseWorker::MAIN_BRANCH_NAME,
    ): string {
        return 'Check that correct version string has been entered.';
    }

    /**
     * @return string[]
     */
    #[Override]
    protected function getAllowedStages(): array
    {
        return Stage::getAllStages();
    }

    #[Override]
    public function work(
        Version $version,
        string $initialBranchName = AbstractShopsysReleaseWorker::MAIN_BRANCH_NAME,
    ): void {
        if (!str_starts_with($version->getOriginalString(), 'v')) {
            throw new RuntimeException(
                'Name of released version must start with \'v\'',
            );
        }

        if ($version->getOriginalString() !== 'v' . $version->getVersionString()) {
            throw new RuntimeException(
                'Version string needs to follow SemVer format (e.g. v11.0.0)',
            );
        }
    }
}
