<?php

declare(strict_types=1);

namespace Shopsys\Releaser\ReleaseWorker\AfterRelease;

use PharIo\Version\Version;
use Shopsys\Releaser\ReleaseWorker\AbstractCheckShopsysInstallReleaseWorker;
use Shopsys\Releaser\Stage;

final class CheckShopsysInstallReleaseWorker extends AbstractCheckShopsysInstallReleaseWorker
{
    /**
     * @param \PharIo\Version\Version $version
     * @return string
     */
    public function getDescription(Version $version): string
    {
        return '[Manually] Install Shopsys Framework (project-base) using installation guides on all supported operating systems. You need to wait with the installation until the monorepo is split.';
    }

    /**
     * @return string
     */
    public function getStage(): string
    {
        return Stage::AFTER_RELEASE;
    }
}
