<?php

declare(strict_types=1);

namespace Shopsys\Releaser\ReleaseWorker\Release;

use PharIo\Version\Version;
use Shopsys\Releaser\ReleaseWorker\AbstractShopsysReleaseWorker;
use Shopsys\Releaser\Stage;

final class CheckoutToReleaseCandidateBranchReleaseWorker extends AbstractShopsysReleaseWorker
{
    /**
     * @return int
     */
    public function getPriority(): int
    {
        return 680;
    }

    /**
     * @return string
     */
    public function getStage(): string
    {
        return Stage::RELEASE;
    }

    /**
     * @param \PharIo\Version\Version $version
     * @return string
     */
    public function getDescription(Version $version): string
    {
        return sprintf('Checkout to "%s" branch', $this->createBranchName($version));
    }

    /**
     * @param \PharIo\Version\Version $version
     */
    public function work(Version $version): void
    {
        $this->processRunner->run('git checkout ' . $this->createBranchName($version));
    }
}
