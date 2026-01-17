<?php

declare(strict_types=1);

namespace Shopsys\Releaser\ReleaseWorker\ReleaseCandidate;

use Override;
use PharIo\Version\Version;
use Shopsys\Releaser\ReleaseWorker\AbstractSetMutualDependenciesToVersionReleaseWorker;
use Shopsys\Releaser\Stage;

final class SetMutualDependenciesToVersionReleaseWorker extends AbstractSetMutualDependenciesToVersionReleaseWorker
{
    #[Override]
    protected function getVersionString(Version $version): string
    {
        return 'dev-' . $this->currentBranchName . ' as ' . $version->getVersionString();
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
