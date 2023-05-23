<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Twig;

use Shopsys\FrameworkBundle\Model\Localization\Localization;
use Symfony\Component\Asset\Packages;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class LocalizationExtension extends AbstractExtension
{
    /**
     * @param string $webDir
     * @param \Symfony\Component\Asset\Packages $assetPackages
     * @param \Shopsys\FrameworkBundle\Model\Localization\Localization $localization
     */
    public function __construct(
        protected readonly string $webDir,
        protected readonly Packages $assetPackages,
        protected readonly Localization $localization,
    ) {
    }

    /**
     * @return \Twig\TwigFunction[]
     */
    public function getFunctions()
    {
        return [
            new TwigFunction('localeFlag', [$this, 'getLocaleFlagHtml'], ['is_safe' => ['html']]),
        ];
    }

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

    /**
     * @param string $locale
     * @return string
     */
    protected function getTitle($locale)
    {
        return $this->localization->getLanguageName($locale);
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'localization';
    }
}
