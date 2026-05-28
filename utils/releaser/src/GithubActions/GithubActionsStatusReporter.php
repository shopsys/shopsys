<?php

declare(strict_types=1);

namespace Shopsys\Releaser\GithubActions;

use Shopsys\Releaser\Guzzle\ApiCaller;
use Shopsys\Releaser\Packagist\PackageProvider;
use Shopsys\Releaser\ReleaseWorker\AbstractShopsysReleaseWorker;
use Throwable;

final class GithubActionsStatusReporter
{
    public const string STATUS_PENDING = 'pending';
    public const string STATUS_IN_PROGRESS = 'in_progress';
    public const string STATUS_SUCCESS = 'success';

    private const string WORKFLOW_FILE = 'run-checks-tests.yaml';

    /**
     * Packages that are not on Packagist, so unable to found by API, but also running on GitHub Actions
     *
     * @var string[]
     */
    private const array EXTRA_PACKAGES = [];

    /**
     * Packages that are on Packagist, but the GitHub Actions are not run on them
     */
    private const array IGNORED_PACKAGES = [
        'shopsys/deployment',
        'shopsys/biome-config',
    ];

    public function __construct(
        private readonly PackageProvider $packageProvider,
        private readonly ApiCaller $apiCaller,
    ) {
    }

    /**
     * @return array<string, string> map of package name => effective GitHub Actions status
     */
    public function getStatusForPackagesByOrganizationAndBranch(
        string $organization,
        string $branch,
        string $githubToken,
    ): array {
        $packages = $this->packageProvider->getPackagesByOrganization(
            $organization,
            AbstractShopsysReleaseWorker::EXCLUDED_PACKAGES,
        );
        $packages = array_values(array_diff(array_merge($packages, self::EXTRA_PACKAGES), self::IGNORED_PACKAGES));
        $statusForPackages = array_fill_keys($packages, self::STATUS_PENDING);

        $branchHeadShasByPackage = $this->getBranchHeadShasByPackage($packages, $branch, $githubToken);
        $responses = $this->apiCaller->sendGetsAsyncToStrings(
            $this->createWorkflowRunsApiUrls($packages, $branch, self::WORKFLOW_FILE),
            $this->createGithubApiHeaders($githubToken),
        );

        foreach ($responses as $key => $response) {
            $package = $packages[$key];

            $statusForPackages[$package] = $this->extractWorkflowRunStatus(
                $response,
                $branchHeadShasByPackage[$package] ?? null,
            );
        }

        return $statusForPackages;
    }

    /**
     * Returns the effective status of the latest workflow_runs[0] for the given repository, branch and workflow file.
     * Returns STATUS_PENDING when the latest run does not match the branch HEAD SHA (so older runs from previous
     * pushes never produce a false-positive STATUS_SUCCESS), the run is still queued/in-progress, or any upstream
     * lookup fails.
     */
    public function getStatusForRepositoryWorkflow(
        string $repository,
        string $branch,
        string $workflowFileName,
        string $githubToken,
    ): string {
        $branchHeadSha = $this->fetchBranchHeadSha($repository, $branch, $githubToken);

        if ($branchHeadSha === null) {
            return self::STATUS_PENDING;
        }

        $responses = $this->apiCaller->sendGetsAsyncToStrings(
            $this->createWorkflowRunsApiUrls([$repository], $branch, $workflowFileName),
            $this->createGithubApiHeaders($githubToken),
        );

        return $this->extractWorkflowRunStatus($responses[0] ?? '', $branchHeadSha);
    }

    /**
     * @param string[] $packages
     * @return array<string, string>
     */
    private function getBranchHeadShasByPackage(array $packages, string $branch, string $githubToken): array
    {
        $responses = $this->apiCaller->sendGetsAsyncToStrings(
            $this->createBranchApiUrls($packages, $branch),
            $this->createGithubApiHeaders($githubToken),
        );
        $branchHeadShasByPackage = [];

        foreach ($responses as $key => $response) {
            $branchHeadSha = $this->extractBranchHeadSha($response);

            if ($branchHeadSha === null) {
                continue;
            }

            $branchHeadShasByPackage[$packages[$key]] = $branchHeadSha;
        }

        return $branchHeadShasByPackage;
    }

    /**
     * @param string[] $packages
     * @return string[]
     */
    private function createWorkflowRunsApiUrls(array $packages, string $branch, string $workflowFileName): array
    {
        $apiUrls = [];
        $encodedBranch = rawurlencode($branch);

        foreach ($packages as $package) {
            $apiUrls[] = sprintf(
                'https://api.github.com/repos/%s/actions/workflows/%s/runs?per_page=1&branch=%s',
                $package,
                $workflowFileName,
                $encodedBranch,
            );
        }

        return $apiUrls;
    }

    private function fetchBranchHeadSha(string $repository, string $branch, string $githubToken): ?string
    {
        $responses = $this->apiCaller->sendGetsAsyncToStrings(
            $this->createBranchApiUrls([$repository], $branch),
            $this->createGithubApiHeaders($githubToken),
        );

        return $this->extractBranchHeadSha($responses[0] ?? '');
    }

    /**
     * @param string[] $packages
     * @return string[]
     */
    private function createBranchApiUrls(array $packages, string $branch): array
    {
        $apiUrls = [];
        $encodedBranch = rawurlencode($branch);

        foreach ($packages as $package) {
            $apiUrls[] = sprintf('https://api.github.com/repos/%s/branches/%s', $package, $encodedBranch);
        }

        return $apiUrls;
    }

    /**
     * @return array<string, string>
     */
    private function createGithubApiHeaders(string $githubToken): array
    {
        return ['Authorization' => sprintf('token %s', $githubToken)];
    }

    private function extractBranchHeadSha(string $responseJson): ?string
    {
        try {
            $arrayResponse = json_decode($responseJson, true, 512, JSON_THROW_ON_ERROR);
        } catch (Throwable) {
            return null;
        }

        $sha = $arrayResponse['commit']['sha'] ?? null;

        return is_string($sha) ? $sha : null;
    }

    private function extractWorkflowRunStatus(string $responseJson, ?string $expectedHeadSha): string
    {
        if ($expectedHeadSha === null) {
            return self::STATUS_PENDING;
        }

        try {
            $arrayResponse = json_decode($responseJson, true, 512, JSON_THROW_ON_ERROR);
        } catch (Throwable) {
            return self::STATUS_PENDING;
        }

        if (($arrayResponse['total_count'] ?? 0) === 0 || !isset($arrayResponse['workflow_runs'][0])) {
            return self::STATUS_PENDING;
        }

        $lastRun = $arrayResponse['workflow_runs'][0];

        if (($lastRun['head_sha'] ?? null) !== $expectedHeadSha) {
            return self::STATUS_PENDING;
        }

        $conclusion = $lastRun['conclusion'] ?? null;

        if (!is_string($conclusion) || $conclusion === '') {
            return self::STATUS_IN_PROGRESS;
        }

        return $conclusion;
    }
}
