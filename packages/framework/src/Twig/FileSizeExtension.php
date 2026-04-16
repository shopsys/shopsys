<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Twig;

use Override;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class FileSizeExtension extends AbstractExtension
{
    /**
     * @return \Twig\TwigFilter[]
     */
    #[Override]
    public function getFilters(): array
    {
        return [
            new TwigFilter('formatFileSize', $this->formatFileSize(...)),
        ];
    }

    public function formatFileSize(?int $bytes): string
    {
        if ($bytes === null) {
            return '';
        }

        if ($bytes === 0) {
            return '0 B';
        }

        $units = ['B', 'KB', 'MB', 'GB'];
        $k = 1024;
        $i = (int)floor(log($bytes) / log($k));
        $i = min($i, count($units) - 1);

        return round($bytes / $k ** $i, 1) . ' ' . $units[$i];
    }
}
