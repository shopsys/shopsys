<?php

declare(strict_types=1);

namespace App\Twig;

use Shopsys\FrameworkBundle\Twig\LocalizationExtension as BaseLocalizationExtension;

class LocalizationExtension extends BaseLocalizationExtension
{
    /**
     * @param string $locale
     * @param bool $showTitle
     * @return string
     */
    public function getLocaleFlagHtml($locale, $showTitle = true)
    {
        $filepath = 'public/admin/images/flags/' . $locale . '.png';
        $src = $this->assetPackages->getUrl($filepath);

        if (file_exists($this->webDir . '/' . $filepath) === false) {
            return strtoupper($locale);
        }

        if ($showTitle) {
            $title = $this->getTitle($locale);
            $html = '<img src="' . htmlspecialchars($src, ENT_QUOTES)
                . '" alt="' . htmlspecialchars($locale, ENT_QUOTES)
                . '" title="' . htmlspecialchars($title, ENT_QUOTES) . '" width="16" height="11" />';
        } else {
            $html = '<img src="' . htmlspecialchars($src, ENT_QUOTES)
                . '" alt="' . htmlspecialchars($locale, ENT_QUOTES) . '" width="16" height="11" />';
        }

        return $html;
    }
}
