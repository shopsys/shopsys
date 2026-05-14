<?php

declare(strict_types=1);

namespace Shopsys\Releaser\Wait;

use Override;
use Shopsys\Releaser\GithubActions\GithubActionsStatusReporter;
use Shopsys\Releaser\ReleaseWorker\WaitForExternalConditionInterface;

final class GithubActionsRunSucceeded implements WaitForExternalConditionInterface
{
    private const int POLL_INTERVAL_SECONDS = 60;
    private const string STATUS_SUCCESS = 'success';

    /**
     * @var array<string, string>
     */
    private array $lastStatuses = [];

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
        $this->lastStatuses = $this->statusFetcher->getStatusForPackagesByOrganizationAndBranch(
            $this->organization,
            $this->branch,
            $this->githubToken,
        );

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
        if ($this->lastStatuses === []) {
            return sprintf('no GitHub Actions results reported yet for %s on %s', $this->organization, $this->branch);
        }

        $failing = $this->getFailingPackages();

        if ($failing === []) {
            return sprintf('all %d packages green, awaiting confirmation', count($this->lastStatuses));
        }

        $parts = [];

        foreach ($failing as $package => $status) {
            $parts[] = sprintf('%s (%s)', $package, $status);
        }

        return 'still failing: ' . implode(', ', $parts);
    }

    /**
     * @return array<string, string>
     */
    private function getFailingPackages(): array
    {
        $failing = [];

        foreach ($this->lastStatuses as $package => $status) {
            if ($status !== self::STATUS_SUCCESS) {
                $failing[$package] = $status;
            }
        }

        return $failing;
    }
}
