<?php

declare(strict_types=1);

namespace Shopsys\Releaser\ReleaseWorker;

use PharIo\Version\Version;

interface StageWorkerInterface
{
    /**
     * 1 line description of what this worker does, in a commanding form! e.g.:
     * - "Add new tag"
     * - "Dump new items to CHANGELOG.md"
     * - "Run coding standards"
     */
    public function getDescription(
        Version $version,
        string $initialBranchName = AbstractShopsysReleaseWorker::MAIN_BRANCH_NAME,
    ): string;

    public function work(
        Version $version,
        string $initialBranchName = AbstractShopsysReleaseWorker::MAIN_BRANCH_NAME,
    ): void;

    public function belongToStage(string $stage): bool;
}
