<?php

declare(strict_types=1);

namespace Shopsys\MonorepoTools\MutualDependency;

use Nette\Utils\Json;
use Shopsys\Releaser\FilesProvider\ComposerJsonFilesProvider;

/**
 * Verifies that every package directly requires all of its transitive shopsys dependencies that are required in a
 * development version (e.g. "20.0.x-dev").
 *
 * Composer derives a per-package stability flag from a direct dev-version require in the root composer.json, but it
 * does not derive it for a dev-version dependency that is only reachable transitively. Therefore a split package
 * tested standalone fails to install (the transitive dev dependency "does not match your minimum-stability") unless
 * it lists that dependency directly.
 */
class MutualDependencyChecker
{
    public function __construct(
        private readonly ComposerJsonFilesProvider $composerJsonFilesProvider,
    ) {
    }

    /**
     * @return \Shopsys\MonorepoTools\MutualDependency\MissingMutualDependencies[]
     */
    public function check(): array
    {
        $fileInfoByName = [];
        $requiresByName = [];

        foreach ($this->composerJsonFilesProvider->provideExcludingMonorepoComposerJson() as $composerJsonFileInfo) {
            $jsonContent = Json::decode($composerJsonFileInfo->getContents(), true);
            $packageName = $jsonContent['name'];

            $fileInfoByName[$packageName] = $composerJsonFileInfo;
            $requiresByName[$packageName] = $jsonContent['require'] ?? [];
        }

        $internalPackageNames = array_keys($requiresByName);

        $missingMutualDependencies = [];

        foreach ($internalPackageNames as $packageName) {
            $missingRequiresByVersion = $this->resolveMissingRequires(
                $packageName,
                $requiresByName,
                $internalPackageNames,
            );

            if ($missingRequiresByVersion === []) {
                continue;
            }

            ksort($missingRequiresByVersion);

            $missingMutualDependencies[] = new MissingMutualDependencies(
                $packageName,
                $fileInfoByName[$packageName],
                $missingRequiresByVersion,
            );
        }

        return $missingMutualDependencies;
    }

    /**
     * @param string $packageName
     * @param array<string, array<string, string>> $requiresByName
     * @param string[] $internalPackageNames
     * @return array<string, string>
     */
    private function resolveMissingRequires(
        string $packageName,
        array $requiresByName,
        array $internalPackageNames,
    ): array {
        $directRequires = $requiresByName[$packageName];
        $transitiveDevRequires = $this->resolveTransitiveDevRequires(
            $packageName,
            $requiresByName,
            $internalPackageNames,
        );

        $missingRequiresByVersion = [];

        foreach ($transitiveDevRequires as $dependencyName => $developmentVersion) {
            if (array_key_exists($dependencyName, $directRequires)) {
                continue;
            }

            $missingRequiresByVersion[$dependencyName] = $developmentVersion;
        }

        return $missingRequiresByVersion;
    }

    /**
     * Walks the production "require" graph (never "require-dev", mirroring how Composer propagates stability flags) and
     * collects every internal shopsys package reachable through dev-version edges.
     *
     * @param string $rootPackageName
     * @param array<string, array<string, string>> $requiresByName
     * @param string[] $internalPackageNames
     * @return array<string, string> map of reachable package name to the development version constraint seen on its edge
     */
    private function resolveTransitiveDevRequires(
        string $rootPackageName,
        array $requiresByName,
        array $internalPackageNames,
    ): array {
        $reachableDevRequires = [];
        $visitedPackageNames = [];
        $packageNamesToVisit = [$rootPackageName];

        while ($packageNamesToVisit !== []) {
            $currentPackageName = array_pop($packageNamesToVisit);

            if (array_key_exists($currentPackageName, $visitedPackageNames)) {
                continue;
            }

            $visitedPackageNames[$currentPackageName] = true;

            foreach ($requiresByName[$currentPackageName] ?? [] as $requiredName => $versionConstraint) {
                if (!in_array($requiredName, $internalPackageNames, true)) {
                    continue;
                }

                if (!$this->isDevelopmentVersion($versionConstraint)) {
                    continue;
                }

                if ($requiredName !== $rootPackageName) {
                    $reachableDevRequires[$requiredName] = $versionConstraint;
                }

                $packageNamesToVisit[] = $requiredName;
            }
        }

        return $reachableDevRequires;
    }

    private function isDevelopmentVersion(string $versionConstraint): bool
    {
        $versionConstraint = trim($versionConstraint);

        return str_contains($versionConstraint, 'x-dev') || str_starts_with($versionConstraint, 'dev-');
    }
}
