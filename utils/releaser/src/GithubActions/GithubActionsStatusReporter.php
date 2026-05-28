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

        $refHeadShasByPackage = $this->getRefHeadShasByPackage($packages, $branch, $githubToken);
        $responses = $this->apiCaller->sendGetsAsyncToStrings(
            $this->createWorkflowRunsApiUrls($packages, $branch, self::WORKFLOW_FILE),
            $this->createGithubApiHeaders($githubToken),
        );

        foreach ($responses as $key => $response) {
            $package = $packages[$key];

            $statusForPackages[$package] = $this->extractWorkflowRunStatus(
                $response,
                $refHeadShasByPackage[$package] ?? null,
            );
        }

        return $statusForPackages;
    }

    /**
     * Returns the effective status of the latest workflow_runs[0] for the given repository, ref and workflow file.
     * Returns STATUS_PENDING when the latest run does not match the ref HEAD SHA (so older runs from previous
     * pushes never produce a false-positive STATUS_SUCCESS), the run is still queued/in-progress, or any upstream
     * lookup fails.
     */
    public function getStatusForRepositoryWorkflow(
        string $repository,
        string $branch,
        string $workflowFileName,
        string $githubToken,
    ): string {
        $refHeadSha = $this->fetchRefHeadSha($repository, $branch, $githubToken);

        if ($refHeadSha === null) {
            return self::STATUS_PENDING;
        }

        $responses = $this->apiCaller->sendGetsAsyncToStrings(
            $this->createWorkflowRunsApiUrls([$repository], $branch, $workflowFileName),
            $this->createGithubApiHeaders($githubToken),
        );

        return $this->extractWorkflowRunStatus($responses[0] ?? '', $refHeadSha);
    }

    /**
     * @param string[] $packages
     * @return array<string, string>
     */
    private function getRefHeadShasByPackage(array $packages, string $ref, string $githubToken): array
    {
        $responses = $this->apiCaller->sendGetsAsyncToStrings(
            $this->createCommitApiUrls($packages, $ref),
            $this->createGithubApiHeaders($githubToken),
        );
        $refHeadShasByPackage = [];

        foreach ($responses as $key => $response) {
            $refHeadSha = $this->extractCommitSha($response);

            if ($refHeadSha === null) {
                continue;
            }

            $refHeadShasByPackage[$packages[$key]] = $refHeadSha;
        }

        return $refHeadShasByPackage;
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

    private function fetchRefHeadSha(string $repository, string $ref, string $githubToken): ?string
    {
        $responses = $this->apiCaller->sendGetsAsyncToStrings(
            $this->createCommitApiUrls([$repository], $ref),
            $this->createGithubApiHeaders($githubToken),
        );

        return $this->extractCommitSha($responses[0] ?? '');
    }

    /**
     * @param string[] $packages
     * @return string[]
     */
    private function createCommitApiUrls(array $packages, string $ref): array
    {
        $apiUrls = [];
        $encodedRef = rawurlencode($ref);

        foreach ($packages as $package) {
            $apiUrls[] = sprintf('https://api.github.com/repos/%s/commits/%s', $package, $encodedRef);
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

    private function extractCommitSha(string $responseJson): ?string
    {
        try {
            $arrayResponse = json_decode($responseJson, true, 512, JSON_THROW_ON_ERROR);
        } catch (Throwable) {
            return null;
        }

        $sha = $arrayResponse['sha'] ?? null;

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
