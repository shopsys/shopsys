<?php

declare(strict_types=1);

namespace Shopsys\Releaser\Packagist;

use Nette\Utils\FileSystem;
use Nette\Utils\Json;
use Shopsys\Releaser\Exception\ShouldNotHappenException;
use Throwable;

final class PackageProvider
{
    /**
     * @var string
     */
    private const string PACKAGE_NAMES = 'packageNames';

    /**
     * @param string[] $excludePackages
     * @return string[]
     */
    public function getPackagesByOrganization(string $organization, array $excludePackages = []): array
    {
        $url = 'https://packagist.org/packages/list.json?vendor=' . $organization;
        $remoteContent = FileSystem::read($url);
        $json = Json::decode($remoteContent, true);

        $this->ensureIsValidResponse($json, $url);

        return $this->filterOutExcludedPackages($json[self::PACKAGE_NAMES], $excludePackages);
    }

    /**
     * @param string[] $excludedPackages
     * @return mixed[]
     */
    public function getPackagesWithVersionsByOrganization(string $organization, array $excludedPackages = []): array
    {
        $packages = $this->getPackagesByOrganization($organization, $excludedPackages);
        $packagesWithVersions = [];

        foreach ($packages as $package) {
            $packagesWithVersions[$package] = $this->getPackageVersions($package);
        }

        return $packagesWithVersions;
    }

    public function hasVersion(string $package, string $version): bool
    {
        try {
            $publishedVersions = $this->getPackageVersions($package);
        } catch (Throwable) {
            return false;
        }

        return in_array($version, $publishedVersions, true);
    }

    private function ensureIsValidResponse(array $json, string $url): void
    {
        if (isset($json[self::PACKAGE_NAMES])) {
            return;
        }

        throw new ShouldNotHappenException(
            'Packagist API failed to list package names for url request:' . PHP_EOL . $url,
        );
    }

    /**
     * @return string[]
     */
    private function getPackageVersions(string $package): array
    {
        $url = 'https://repo.packagist.org/p2/' . $package . '.json';
        $remoteContent = FileSystem::read($url);
        $json = Json::decode($remoteContent, true);

        if (!isset($json['packages'][$package])) {
            return [];
        }

        return array_map(static function ($pkg) { return $pkg['version']; }, $json['packages'][$package]);
    }

    /**
     * @param string[] $packages
     * @param string[] $excludePackages
     * @return string[]
     */
    private function filterOutExcludedPackages(array $packages, array $excludePackages): array
    {
        if ($excludePackages === []) {
            return $packages;
        }

        foreach ($packages as $key => $package) {
            if (in_array($package, $excludePackages, true)) {
                unset($packages[$key]);
            }
        }

        return $packages;
    }
}
