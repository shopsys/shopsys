<?php

declare(strict_types=1);

namespace Shopsys\MonorepoTools\Tests\MutualDependency;

use PHPUnit\Framework\TestCase;
use Shopsys\MonorepoTools\MutualDependency\MutualDependencyChecker;
use Shopsys\Releaser\FilesProvider\ComposerJsonFilesProvider;

final class MutualDependencyCheckerTest extends TestCase
{
    private function createChecker(): MutualDependencyChecker
    {
        return new MutualDependencyChecker(
            new ComposerJsonFilesProvider([__DIR__ . '/../Fixtures/packages']),
        );
    }

    public function testReportsTransitiveDevDependenciesMissingFromDirectRequires(): void
    {
        $results = $this->createChecker()->check();

        $missingByPackageName = [];

        foreach ($results as $result) {
            $missingByPackageName[$result->packageName] = $result->missingRequiresByVersion;
        }

        // "root-with-missing-dependencies" directly requires only "intermediate"; through it, the dev-version
        // "leaf-a" and "leaf-b" are reachable transitively but not declared directly, so both must be reported
        $this->assertArrayHasKey('fixture/root-with-missing-dependencies', $missingByPackageName);
        $this->assertSame(
            [
                'fixture/leaf-a' => '1.0.x-dev',
                'fixture/leaf-b' => '1.0.x-dev',
            ],
            $missingByPackageName['fixture/root-with-missing-dependencies'],
        );
    }

    public function testDoesNotReportPackageThatAlreadyDeclaresAllTransitiveDevDependencies(): void
    {
        $results = $this->createChecker()->check();

        $reportedPackageNames = array_map(static fn ($result) => $result->packageName, $results);

        $this->assertNotContains('fixture/root-with-all-dependencies', $reportedPackageNames);
    }

    public function testDoesNotReportTransitiveDependencyReachableOnlyThroughStableVersionEdge(): void
    {
        $results = $this->createChecker()->check();

        $reportedPackageNames = array_map(static fn ($result) => $result->packageName, $results);

        // "root-with-stable-dependency" requires "intermediate" in a stable constraint ("^1.0"), so the rule does
        // not apply (this is the released state, where stability flags are irrelevant)
        $this->assertNotContains('fixture/root-with-stable-dependency', $reportedPackageNames);
    }
}
