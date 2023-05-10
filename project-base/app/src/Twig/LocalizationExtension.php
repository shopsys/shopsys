<?php

declare(strict_types=1);

namespace App\Twig;

use Shopsys\FrameworkBundle\Twig\LocalizationExtension as BaseLocalizationExtension;

class LocalizationExtension extends BaseLocalizationExtension
{
    /**
     * @param string $locale
     * @param bool $showTitle
     * @param int $width
     * @param int $height
     * @return string
     */
    public function getLocaleFlagHtml($locale, $showTitle = true, int $width = 16, int $height = 11): string
    {
        $filepath = sprintf('public/admin/images/flags/%s.png', $locale);

        if (file_exists(sprintf('%s/%s', $this->webDir, $filepath)) === false) {
            return strtoupper($locale);
        }

        $src = $this->assetPackages->getUrl($filepath);

        if ($showTitle) {
            return sprintf(
                '<img src="%s" alt="%s" title="%s" width="%d" height="%d" />',
                htmlspecialchars($src, ENT_QUOTES),
                htmlspecialchars($locale, ENT_QUOTES),
                htmlspecialchars($this->getTitle($locale), ENT_QUOTES),
                $width,
                $height
            );
        }

        return sprintf(
            '<img src="%s" alt="%s" width="%d" height="%d" />',
            htmlspecialchars($src, ENT_QUOTES),
            htmlspecialchars($locale, ENT_QUOTES),
            $width,
            $height
        );
    }
}
