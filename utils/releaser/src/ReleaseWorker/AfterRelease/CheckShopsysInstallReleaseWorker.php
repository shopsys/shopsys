<?php

declare(strict_types=1);

namespace Shopsys\Releaser\ReleaseWorker\AfterRelease;

use Shopsys\Releaser\ReleaseWorker\AbstractCheckShopsysInstallReleaseWorker;
use Shopsys\Releaser\Stage;

final class CheckShopsysInstallReleaseWorker extends AbstractCheckShopsysInstallReleaseWorker
{
    /**
     * @return string
     */
    public function getStage(): string
    {
        return Stage::AFTER_RELEASE;
    }
}
