<?php

declare(strict_types=1);

namespace Shopsys\Releaser\FileManipulator;

use Nette\Utils\FileSystem;
use Nette\Utils\Json;
use Symfony\Component\Finder\SplFileInfo;

class ComposerJsonFileManipulator
{
    /**
     * Adds missing entries into the "require" section, keeping the keys of the same vendor alphabetically ordered.
     *
     * @param array<string, string> $requiresByVersion map of package name to version constraint to add
     */
    public function addRequires(SplFileInfo $fileInfo, array $requiresByVersion): void
    {
        $jsonContent = Json::decode($fileInfo->getContents(), true);
        $require = $jsonContent['require'] ?? [];

        foreach ($requiresByVersion as $packageName => $versionConstraint) {
            $require = $this->insertRequireWithinVendor($require, $packageName, $versionConstraint);
        }

        $jsonContent['require'] = $require;

        $fileContent = Json::encode($jsonContent, pretty: true) . PHP_EOL;
        FileSystem::write($fileInfo->getRealPath(), $fileContent);
    }

    /**
     * @param array<string, string> $require
     * @return array<string, string>
     */
    private function insertRequireWithinVendor(
        array $require,
        string $newPackageName,
        string $versionConstraint,
    ): array {
        if (array_key_exists($newPackageName, $require)) {
            return $require;
        }

        $vendorPrefix = explode('/', $newPackageName, 2)[0] . '/';
        $sameVendorPackageNames = array_filter(
            array_keys($require),
            static fn (string $packageName): bool => str_starts_with($packageName, $vendorPrefix),
        );

        $insertBeforePackageName = null;

        foreach ($sameVendorPackageNames as $sameVendorPackageName) {
            if (strcmp($sameVendorPackageName, $newPackageName) > 0) {
                $insertBeforePackageName = $sameVendorPackageName;

                break;
            }
        }

        // the new package sorts after all of its vendor siblings (or there are none yet),
        // so it is appended right after the last sibling, keeping the vendor block together
        $insertAfterPackageName = $sameVendorPackageNames === [] ? null : end($sameVendorPackageNames);

        $result = [];

        foreach ($require as $packageName => $existingVersionConstraint) {
            if ($packageName === $insertBeforePackageName) {
                $result[$newPackageName] = $versionConstraint;
            }

            $result[$packageName] = $existingVersionConstraint;

            if ($insertBeforePackageName === null && $packageName === $insertAfterPackageName) {
                $result[$newPackageName] = $versionConstraint;
            }
        }

        if (!array_key_exists($newPackageName, $result)) {
            $result[$newPackageName] = $versionConstraint;
        }

        return $result;
    }

    /**
     * @param \Symfony\Component\Finder\SplFileInfo[] $fileInfos
     * @param string[] $packageNames
     */
    public function setMutualDependenciesToVersion(
        array $fileInfos,
        array $packageNames,
        string $version,
    ): void {
        foreach ($fileInfos as $fileInfo) {
            $jsonContent = Json::decode($fileInfo->getContents(), true);

            foreach ($packageNames as $packageName) {
                $jsonContent = $this->replaceVersion($jsonContent, $packageName, $version);
            }

            $fileContent = Json::encode($jsonContent, pretty: true) . PHP_EOL;
            FileSystem::write($fileInfo->getRealPath(), $fileContent);
        }
    }

    private function replaceVersion(array $jsonContent, string $packageName, string $requestedVersion): array
    {
        if (isset($jsonContent['require'][$packageName])) {
            $jsonContent['require'][$packageName] = $requestedVersion;
        }

        if (isset($jsonContent['require-dev'][$packageName])) {
            $jsonContent['require-dev'][$packageName] = $requestedVersion;
        }

        return $jsonContent;
    }
}
