<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Twig;

use Override;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Domain\DomainFacade;
use Shopsys\FrameworkBundle\Component\Domain\Exception\NoDomainSelectedException;
use Symfony\Component\Asset\Packages;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class DomainExtension extends AbstractExtension
{
    protected string $domainImagesUrlPrefix;

    /**
     * @param mixed $domainImagesUrlPrefix
     * @param \Symfony\Component\Asset\Packages $assetPackages
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     * @param \Shopsys\FrameworkBundle\Component\Domain\DomainFacade $domainFacade
     */
    public function __construct(
        $domainImagesUrlPrefix,
        protected readonly Packages $assetPackages,
        protected readonly Domain $domain,
        protected readonly DomainFacade $domainFacade,
    ) {
        $this->domainImagesUrlPrefix = $domainImagesUrlPrefix;
    }

    /**
     * @return \Twig\TwigFunction[]
     */
    #[Override]
    public function getFunctions()
    {
        return [
            new TwigFunction('getDomain', $this->getDomain(...)),
            new TwigFunction('getDomainName', $this->getDomainNameById(...)),
            new TwigFunction('domainIcon', $this->getDomainIconHtml(...), ['is_safe' => ['html']]),
            new TwigFunction('isMultidomain', $this->isMultidomain(...)),
            new TwigFunction('getDomainUrlByLocale', $this->getDomainUrlByLocale(...)),
        ];
    }

    /**
     * @return \Shopsys\FrameworkBundle\Component\Domain\Domain
     */
    public function getDomain()
    {
        return $this->domain;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'domain';
    }

    /**
     * @param int $domainId
     * @return string
     */
    public function getDomainNameById($domainId)
    {
        return $this->getDomain()->getDomainConfigById($domainId)->getName();
    }

    /**
     * @param int $domainId
     * @param string $size
     * @return string
     */
    public function getDomainIconHtml($domainId, $size = 'normal')
    {
        $domainName = $this->getDomain()->getDomainConfigById($domainId)->getName();

        if ($this->domainFacade->existsDomainIcon($domainId)) {
            $src = $this->assetPackages->getUrl(sprintf('%s/%u.png', $this->domainImagesUrlPrefix, $domainId));

            return '
                <span class="in-image in-image--' . $size . '">
                    <span
                        class="in-image__in"
                    >
                        <img src="' . htmlspecialchars($src, ENT_QUOTES)
                        . '" alt="' . htmlspecialchars((string)$domainId, ENT_QUOTES) . '"'
                        . ' title="' . htmlspecialchars($domainName, ENT_QUOTES) . '"/>
                    </span>
                </span>';
        }

        return '
                <span class="in-image in-image--' . $size . '">
                    <span
                        class="in-image__in in-image__in--' . $domainId . '"
                        title="' . htmlspecialchars($domainName, ENT_QUOTES) . '"
                    >' . $domainId . '</span>
                </span>
            ';
    }

    /**
     * @return bool
     */
    public function isMultidomain()
    {
        return $this->getDomain()->isMultidomain();
    }

    /**
     * @param string $locale
     * @return string
     */
    public function getDomainUrlByLocale(string $locale): string
    {
        foreach ($this->domain->getAll() as $domain) {
            if ($domain->getLocale() === $locale) {
                return $domain->getUrl();
            }
        }

        throw new NoDomainSelectedException('Domain for locale `' . $locale . '` not found;');
    }
}
