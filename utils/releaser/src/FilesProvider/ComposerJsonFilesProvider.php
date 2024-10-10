<?php

declare(strict_types=1);

namespace Shopsys\Releaser\FilesProvider;

use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;

class ComposerJsonFilesProvider
{
    /**
     * @param string[] $packageDirectories
     */
    public function __construct(
        protected array $packageDirectories,
    ) {
    }

    /**
     * @return \Symfony\Component\Finder\SplFileInfo[]
     */
    public function provideAll(): array
    {
        return [
            ...$this->provideExcludingMonorepoComposerJson(),
            new SplFileInfo(dirname(__DIR__, 4) . '/composer.json', '', 'composer.json'),
        ];
    }

    /**
     * @return \Symfony\Component\Finder\SplFileInfo[]
     */
    public function provideExcludingMonorepoComposerJson(): array
    {
        $finder = Finder::create()
            ->files()
            ->ignoreUnreadableDirs()
            ->depth(1)
            ->in($this->packageDirectories)
            ->exclude([
                'vendor',
                '.npm-global',
                'node_modules',
            ])
            ->name('composer.json');

        return iterator_to_array($finder->getIterator());
    }
}
