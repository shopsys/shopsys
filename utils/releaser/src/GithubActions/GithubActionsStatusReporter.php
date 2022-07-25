<?php

declare(strict_types=1);

namespace Shopsys\Releaser\GithubActions;

use Shopsys\Releaser\Guzzle\ApiCaller;
use Shopsys\Releaser\Packagist\PackageProvider;

final class GithubActionsStatusReporter
{
    /**
     * Packages that are not on Packagist, so unable to found by API, but also running on GitHub Actions
     *
     * @var string[]
     */
    private const EXTRA_PACKAGES = [];

    /**
     * Packages that are not tested on GitHub Actions - old packages or forks
     *
     * @var string[]
     */
    private const EXCLUDED_PACKAGES = [
        // forks
        'shopsys/jsformvalidator-bundle',
        'shopsys/changelog-linker',
        'shopsys/doctrine-orm',
        'shopsys/jparser',
        'shopsys/monorepo-builder',
        'shopsys/postgres-search-bundle',
        // old packages
        'shopsys/syscart',
        'shopsys/sysconfig',
        'shopsys/sysreports',
        'shopsys/sysstdlib',
        'shopsys/backend-api',
        'shopsys/phpstorm-inspect',
        'shopsys/product-feed-interface',
    ];

    /**
     * @var \Shopsys\Releaser\Packagist\PackageProvider
     */
    private $packageProvider;

    /**
     * @var \Shopsys\Releaser\Guzzle\ApiCaller
     */
    private $apiCaller;

    /**
     * @var string[]
     */
    private $statusForPackages = [];

    /**
     * @param \Shopsys\Releaser\Packagist\PackageProvider $packageProvider
     * @param \Shopsys\Releaser\Guzzle\ApiCaller $apiCaller
     */
    public function __construct(PackageProvider $packageProvider, ApiCaller $apiCaller)
    {
        $this->packageProvider = $packageProvider;
        $this->apiCaller = $apiCaller;
    }

    /**
     * @param string $organization
     * @param string $branch
     * @param string $githubToken
     * @return string[]
     */
    public function getStatusForPackagesByOrganizationAndBranch(string $organization, string $branch, string $githubToken): array
    {
        $packages = $this->packageProvider->getPackagesByOrganization($organization, self::EXCLUDED_PACKAGES);
        $packages = array_merge($packages, self::EXTRA_PACKAGES);

        $urls = $this->createApiUrls($packages, $branch);

        $responses = $this->apiCaller->sendGetsAsyncToStrings($urls, ['Authorization' => sprintf('token %s', $githubToken)]);

        foreach ($responses as $response) {
            $this->processResponse($response);
        }

        return $this->statusForPackages;
    }

    /**
     * @param string[] $packages
     * @param string $branch
     * @return string[]
     */
    private function createApiUrls(array $packages, string $branch): array
    {
        $apiUrls = [];
        foreach ($packages as $package) {
            $apiUrls[] = sprintf('https://api.github.com/repos/%s/actions/workflows/run-checks-tests.yaml/runs?per_page=1&status=completed&branch=%s', $package, $branch);
        }

        return $apiUrls;
    }

    /**
     * @param string $responseJson
     */
    private function processResponse(string $responseJson): void
    {
        $arrayResponse = json_decode($responseJson, true, 512, JSON_THROW_ON_ERROR);

        if ($arrayResponse['total_count'] === 0) {
            return;
        }

        $lastRun = array_pop($arrayResponse['workflow_runs']);
        $packageName = $lastRun['repository']['full_name'];
        $status = $lastRun['conclusion'];

        $this->statusForPackages[$packageName] = $status;
    }
}
