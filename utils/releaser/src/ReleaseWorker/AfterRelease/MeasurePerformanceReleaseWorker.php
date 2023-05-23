<?php

declare(strict_types=1);

namespace Shopsys\Releaser\ReleaseWorker\AfterRelease;

use PharIo\Version\Version;
use Shopsys\Releaser\ReleaseWorker\AbstractShopsysReleaseWorker;
use Shopsys\Releaser\Stage;

final class MeasurePerformanceReleaseWorker extends AbstractShopsysReleaseWorker
{
    /**
     * @param \PharIo\Version\Version $version
     * @param string $initialBranchName
     * @return string
     */
    public function getDescription(
        Version $version,
        string $initialBranchName = AbstractShopsysReleaseWorker::MAIN_BRANCH_NAME,
    ): string {
        return '[Manually] Measure the performance on Performator';
    }

    /**
     * @param \PharIo\Version\Version $version
     * @param string $initialBranchName
     */
    public function work(
        Version $version,
        string $initialBranchName = AbstractShopsysReleaseWorker::MAIN_BRANCH_NAME,
    ): void {
        $this->symfonyStyle->note(
            'Consider this step for patch version release.
            For other releases do these steps:
            - run branch-stress-test on Heimdall http://heimdall:8080/view/Performance%20tests/job/branch-stress-tests/
            - look at the results at workspace http://heimdall:8080/view/Performance%20tests/job/branch-stress-tests/ws/results/
            - add mean values for 1 and 32 users to https://docs.google.com/spreadsheets/d/1su0ARnJh0zySXb6vMd4TaGBixAzhXR6pHqij25j2zxg/edit#gid=0',
        );
        $this->confirm('Confirm the performance test is finished');
    }

    /**
     * @return string
     */
    public function getStage(): string
    {
        return Stage::AFTER_RELEASE;
    }
}
