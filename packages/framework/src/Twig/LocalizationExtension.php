<?php

namespace Shopsys\FrameworkBundle\Twig;

use Shopsys\FrameworkBundle\Model\Localization\Localization;
use Symfony\Component\Asset\Packages;
use Twig_SimpleFunction;

class LocalizationExtension extends \Twig_Extension
{

    /**
     * @var \Shopsys\FrameworkBundle\Model\Localization\Localization
     */
    private $localization;

    /**
     * @var \Symfony\Component\Asset\Packages
     */
    private $assetPackages;

    public function __construct(Packages $assetPackages, Localization $localization)
    {
        $this->assetPackages = $assetPackages;
        $this->localization = $localization;
    }

    public function getFunctions()
    {
        return [
            new Twig_SimpleFunction('localeFlag', [$this, 'getLocaleFlagHtml'], ['is_safe' => ['html']]),
        ];
    }
    
    public function getLocaleFlagHtml(string $locale, bool $showTitle = true): string
    {
        $src = $this->assetPackages->getUrl('assets/admin/images/flags/' . $locale . '.png');

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
    
    private function getTitle(string $locale): string
    {
        try {
            $title = $this->localization->getLanguageName($locale);
        } catch (\Shopsys\FrameworkBundle\Model\Localization\Exception\InvalidLocaleException $e) {
            $title = '';
        }

        return $title;
    }

    public function getName(): string
    {
        return 'localization';
    }
}
