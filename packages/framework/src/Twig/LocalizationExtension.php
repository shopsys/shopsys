<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Twig;

use Override;
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
    #[Override]
    public function getFunctions()
    {
        return [
            new TwigFunction('localeFlag', $this->getLocaleFlagHtml(...), ['is_safe' => ['html']]),
            new TwigFunction('languageName', $this->getTitle(...), ['is_safe' => ['html']]),
        ];
    }

    /**
     * @param string $locale
     * @param string|null $displayLocale
     * @param bool $showTitle
     * @param int $width
     * @param int $height
     * @return string
     */
    public function getLocaleFlagHtml(
        string $locale,
        ?string $displayLocale = null,
        bool $showTitle = true,
        int $width = 16,
        int $height = 11,
    ): string {
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
                htmlspecialchars($this->getTitle($locale, $displayLocale), ENT_QUOTES),
                $width,
                $height,
            );
        }

        return sprintf(
            '<img src="%s" alt="%s" width="%d" height="%d" />',
            htmlspecialchars($src, ENT_QUOTES),
            htmlspecialchars($locale, ENT_QUOTES),
            $width,
            $height,
        );
    }

    /**
     * @param string $locale
     * @param string|null $displayLocale
     * @return string
     */
    public function getTitle(string $locale, ?string $displayLocale = null): string
    {
        return $this->localization->getLanguageName($locale, $displayLocale);
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'localization';
    }
}
