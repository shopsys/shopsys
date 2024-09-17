<?php

declare(strict_types=1);

namespace Shopsys\Releaser\ReleaseWorker\Release;

use PharIo\Version\Version;
use Shopsys\Releaser\ReleaseWorker\AbstractSetMutualDependenciesToVersionReleaseWorker;
use Shopsys\Releaser\Stage;

final class SetMutualDependenciesToVersionReleaseWorker extends AbstractSetMutualDependenciesToVersionReleaseWorker
{
    /**
     * @param \PharIo\Version\Version $version
     * @return string
     */
    protected function getVersionString(Version $version): string
    {
        return $version->getVersionString();
    }

    /**
     * @return string
     */
    public function getStage(): string
    {
        return Stage::RELEASE;
    }
}
