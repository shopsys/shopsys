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

    /**
     * Tells the worker which 1-based step it is in the stage being executed. Called by the release command
     * before work() so workers that need to reference their own position (e.g. to build a `--resume-step`
     * command for the next worker) can read it from $currentStep instead of re-parsing the stage config.
     */
    public function setCurrentStep(int $currentStep): void;
}
