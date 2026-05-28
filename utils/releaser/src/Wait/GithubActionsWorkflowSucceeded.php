<?php

declare(strict_types=1);

namespace Shopsys\Releaser\Wait;

use Override;
use Shopsys\Releaser\GithubActions\GithubActionsStatusReporter;
use Shopsys\Releaser\ReleaseWorker\WaitForExternalConditionInterface;
use Throwable;

final class GithubActionsWorkflowSucceeded implements WaitForExternalConditionInterface
{
    private const int POLL_INTERVAL_SECONDS = 30;

    private ?string $lastStatus = null;

    private ?string $lastCheckFailureMessage = null;

    public function __construct(
        private readonly GithubActionsStatusReporter $statusReporter,
        private readonly string $organization,
        private readonly string $repository,
        private readonly string $branch,
        private readonly string $workflowFileName,
        private readonly string $githubToken,
    ) {
    }

    #[Override]
    public function describe(): string
    {
        return sprintf(
            'GitHub Actions workflow "%s" success on %s/%s@%s',
            $this->workflowFileName,
            $this->organization,
            $this->repository,
            $this->branch,
        );
    }

    #[Override]
    public function check(): bool
    {
        try {
            $this->lastStatus = $this->statusReporter->getStatusForRepositoryWorkflow(
                sprintf('%s/%s', $this->organization, $this->repository),
                $this->branch,
                $this->workflowFileName,
                $this->githubToken,
            );
            $this->lastCheckFailureMessage = null;
        } catch (Throwable $throwable) {
            $this->lastStatus = null;
            $this->lastCheckFailureMessage = $throwable->getMessage();

            return false;
        }

        return $this->lastStatus === GithubActionsStatusReporter::STATUS_SUCCESS;
    }

    #[Override]
    public function pollIntervalSeconds(): int
    {
        return self::POLL_INTERVAL_SECONDS;
    }

    #[Override]
    public function progressDescription(): string
    {
        if ($this->lastCheckFailureMessage !== null) {
            return sprintf('last check failed: %s', $this->lastCheckFailureMessage);
        }

        if ($this->lastStatus === null) {
            return 'no workflow runs reported yet';
        }

        if ($this->lastStatus === GithubActionsStatusReporter::STATUS_PENDING) {
            return 'no run yet for the current branch HEAD (either no run started, or the latest run is for an older commit)';
        }

        if ($this->lastStatus === GithubActionsStatusReporter::STATUS_IN_PROGRESS) {
            return 'workflow is running on the current branch HEAD';
        }

        return sprintf('workflow concluded with "%s"', $this->lastStatus);
    }
}
