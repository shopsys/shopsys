<?php

declare(strict_types=1);

namespace Shopsys\MonorepoTools\MutualDependency;

use Symfony\Component\Finder\SplFileInfo;

final class MissingMutualDependencies
{
    /**
     * @param string $packageName name of the package whose composer.json is incomplete (e.g. "shopsys/product-feed-mergado")
     * @param \Symfony\Component\Finder\SplFileInfo $composerJsonFileInfo composer.json of the incomplete package
     * @param array<string, string> $missingRequiresByVersion map of the missing direct require to the development
     *      version constraint that should be used (e.g. ["shopsys/mcp-attributes" => "20.0.x-dev"])
     */
    public function __construct(
        public readonly string $packageName,
        public readonly SplFileInfo $composerJsonFileInfo,
        public readonly array $missingRequiresByVersion,
    ) {
    }
}
