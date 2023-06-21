<?php

declare(strict_types=1);

namespace App\Model\Svg;

use DirectoryIterator;

class SvgProvider
{
    private const FILE_EXTENSION_SVG = 'svg';

    /**
     * @param string $svgDirectoryPath
     */
    public function __construct(
        private readonly string $svgDirectoryPath,
    ) {
    }

    /**
     * @return string[]
     */
    public function getAllSvgIconsNames(): array
    {
        $directory = new DirectoryIterator($this->svgDirectoryPath);
        $svgIcons = [];

        foreach ($directory as $item) {
            if ($item->isFile()) {
                $svgIcons[$item->getBasename('.' . self::FILE_EXTENSION_SVG)] = $item->getBasename('.' . self::FILE_EXTENSION_SVG);
            }
        }
        ksort($svgIcons);

        return $svgIcons;
    }
}
