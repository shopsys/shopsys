<?php

namespace Shopsys\Releaser\FilesProvider;

use Symfony\Component\Finder\Finder;
use Symplify\SmartFileSystem\Finder\FinderSanitizer;
use Symplify\SmartFileSystem\SmartFileInfo;

/**
 * Most of the functionality is inspired and copy-pasted from two classes from symplify/monorepo-builder package.
 * We need to include project-base/ folder when looking for composer.json files in monorepo,
 * however, project-base/var folder needs to be excluded from search due to permissions problem.
 * @see \Symplify\MonorepoBuilder\FileSystem\ComposerJsonProvider
 * @see \Symplify\MonorepoBuilder\PackageComposerFinder
 */
class ComposerJsonFilesProvider
{
    /**
     * @var string[]
     */
    protected $packageDirectories;

    /**
     * @var \Symplify\SmartFileSystem\Finder\FinderSanitizer
     */
    protected $finderSanitizer;

    /**
     * @param string[] $packageDirectories
     * @param \Symplify\SmartFileSystem\Finder\FinderSanitizer $finderSanitizer
     */
    public function __construct($packageDirectories, FinderSanitizer $finderSanitizer)
    {
        $this->packageDirectories = $packageDirectories;
        $this->finderSanitizer = $finderSanitizer;
    }

    /**
     * @return \Symplify\SmartFileSystem\SmartFileInfo[]
     */
    public function provideAll(): array
    {
        return array_merge($this->provideExcludingMonorepoComposerJson(), [new SmartFileInfo('composer.json')]);
    }

    /**
     * @return \Symplify\SmartFileSystem\SmartFileInfo[]
     */
    public function provideExcludingMonorepoComposerJson(): array
    {
        $finder = Finder::create()
            ->files()
            ->ignoreUnreadableDirs()
            ->in($this->packageDirectories)
            ->exclude('vendor')
            ->name('composer.json');

        return $this->finderSanitizer->sanitize($finder);
    }
}
