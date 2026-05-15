<?php

declare(strict_types=1);

namespace Shopsys\Releaser\Wait;

use Override;
use Shopsys\Releaser\GithubActions\GithubActionsStatusReporter;
use Shopsys\Releaser\ReleaseWorker\WaitForExternalConditionInterface;
use Throwable;

final class GithubActionsRunSucceeded implements WaitForExternalConditionInterface
{
    private const int POLL_INTERVAL_SECONDS = 60;
    private const string STATUS_SUCCESS = 'success';

    /**
     * @var array<string, string>
     */
    private array $lastStatuses = [];

    private ?string $lastCheckFailureMessage = null;

    public function __construct(
        private readonly GithubActionsStatusReporter $statusFetcher,
        private readonly string $organization,
        private readonly string $branch,
        private readonly string $githubToken,
    ) {
    }

    #[Override]
    public function describe(): string
    {
        return sprintf('GitHub Actions success for all %s packages on branch %s', $this->organization, $this->branch);
    }

    #[Override]
    public function check(): bool
    {
        try {
            $this->lastStatuses = $this->statusFetcher->getStatusForPackagesByOrganizationAndBranch(
                $this->organization,
                $this->branch,
                $this->githubToken,
            );
            $this->lastCheckFailureMessage = null;
        } catch (Throwable $throwable) {
            $this->lastStatuses = [];
            $this->lastCheckFailureMessage = $throwable->getMessage();

            return false;
        }

        if ($this->lastStatuses === []) {
            return false;
        }

        foreach ($this->lastStatuses as $status) {
            if ($status !== self::STATUS_SUCCESS) {
                return false;
            }
        }

        return true;
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
            return sprintf('last GitHub Actions check failed: %s', $this->lastCheckFailureMessage);
        }

        if ($this->lastStatuses === []) {
            return sprintf('no GitHub Actions results reported yet for %s on %s', $this->organization, $this->branch);
        }

        $pendingCount = 0;
        $failing = [];

        foreach ($this->lastStatuses as $package => $status) {
            if ($status === self::STATUS_SUCCESS) {
                continue;
            }

            if ($status === GithubActionsStatusReporter::STATUS_PENDING) {
                $pendingCount++;

                continue;
            }

            $failing[$package] = $status;
        }

        if ($pendingCount === 0 && $failing === []) {
            return sprintf('all %d packages green, awaiting confirmation', count($this->lastStatuses));
        }

        $parts = [];

        if ($pendingCount > 0) {
            $parts[] = sprintf('%d pending', $pendingCount);
        }

        if ($failing !== []) {
            $failingParts = [];

            foreach ($failing as $package => $status) {
                $failingParts[] = sprintf('%s (%s)', $package, $status);
            }

            $parts[] = 'failing: ' . implode(', ', $failingParts);
        }

        return implode('; ', $parts);
    }
}
