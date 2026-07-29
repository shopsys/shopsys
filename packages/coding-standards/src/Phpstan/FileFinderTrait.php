<?php

declare(strict_types=1);

namespace Shopsys\CodingStandards\Phpstan;

use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SplFileInfo;

trait FileFinderTrait
{
    /**
     * @param string[] $directoryPaths
     * @return string[]
     */
    protected function findFilePathsBySuffix(array $directoryPaths, string $fileNameSuffix): array
    {
        $filePaths = [];

        foreach ($directoryPaths as $directoryPath) {
            if (!is_dir($directoryPath)) {
                continue;
            }

            $directoryIterator = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($directoryPath, RecursiveDirectoryIterator::SKIP_DOTS),
            );

            /** @var \SplFileInfo $fileInfo */
            foreach ($directoryIterator as $fileInfo) {
                if ($fileInfo instanceof SplFileInfo && $fileInfo->isFile() && str_ends_with($fileInfo->getFilename(), $fileNameSuffix)) {
                    $filePaths[] = $fileInfo->getPathname();
                }
            }
        }

        return $filePaths;
    }
}
