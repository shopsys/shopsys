<?php

declare(strict_types=1);

namespace Shopsys\Releaser\Travis;

use Shopsys\Releaser\Guzzle\ApiCaller;
use Shopsys\Releaser\Packagist\PackageProvider;

final class TravisStatusReporter
{
    /**
     * Packages that are not on Packagist, so unable to found by API, but also running on Travis
     * @var string[]
     */
    private const EXTRA_PACKAGES = [];

    /**
     * Packages that are not tested on Travis - old packages or forks
     * @var string[]
     */
    private const EXCLUDED_PACKAGES = [
        // forks
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
     * @return string[]
     */
    public function getStatusForPackagesByOrganizationAndBranch(string $organization, string $branch): array
    {
        $packages = $this->packageProvider->getPackagesByOrganization($organization, self::EXCLUDED_PACKAGES);
        $packages = array_merge($packages, self::EXTRA_PACKAGES);

        $urls = $this->createApiUrls($packages, $branch);

        $responses = $this->apiCaller->sendGetsAsyncToStrings($urls);

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
            $apiUrls[] = 'https://api.travis-ci.org/repos/' . $package . '/cc.xml?branch=' . $branch;
        }

        return $apiUrls;
    }

    /**
     * @param string $response
     */
    private function processResponse(string $response): void
    {
        $xmlResponse = simplexml_load_string($response);

        $projectXmlElements = $xmlResponse->xpath('Project');
        if ($projectXmlElements === []) {
            return;
        }

        $projectXmlElement = $projectXmlElements[0];

        $packageName = (string)$projectXmlElement->attributes()->name;
        $status = (string)$projectXmlElement->attributes()->lastBuildStatus;

        $this->statusForPackages[$packageName] = $status;
    }
}
