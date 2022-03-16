<?php

declare(strict_types=1);

namespace Shopsys\Releaser\ReleaseWorker\AfterRelease;

use PharIo\Version\Version;
use Shopsys\Releaser\ReleaseWorker\AbstractCheckPackagesTravisBuildsReleaseWorker;
use Shopsys\Releaser\Stage;

final class CheckPackagesTravisBuildsReleaseWorker extends AbstractCheckPackagesTravisBuildsReleaseWorker
{
    /**
     * @var string
     */
    private $releasingBranchName;

    /**
     * @return string
     */
    public function getStage(): string
    {
        return Stage::AFTER_RELEASE;
    }

    /**
     * @return string
     */
    protected function getBranchName(): string
    {
        return $this->releasingBranchName;
    }

    /**
     * @param \PharIo\Version\Version $version
     */
    public function work(Version $version): void
    {
        $this->releasingBranchName = $version->getVersionString();

        parent::work($version);
    }
}
