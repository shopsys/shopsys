<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Twig;

use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Domain\DomainFacade;
use Symfony\Component\Asset\Packages;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class DomainExtension extends AbstractExtension
{
    protected string $domainImagesUrlPrefix;

    /**
     * @param string $domainImagesUrlPrefix
     * @param \Symfony\Component\Asset\Packages $assetPackages
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     * @param \Shopsys\FrameworkBundle\Component\Domain\DomainFacade $domainFacade
     */
    public function __construct(
        string $domainImagesUrlPrefix,
        protected readonly Packages $assetPackages,
        protected readonly Domain $domain,
        protected readonly DomainFacade $domainFacade,
    ) {
        $this->domainImagesUrlPrefix = $domainImagesUrlPrefix;
    }

    /**
     * @return \Twig\TwigFunction[]
     */
    public function getFunctions(): array
    {
        return [
            new TwigFunction('getDomain', [$this, 'getDomain']),
            new TwigFunction('getDomainName', [$this, 'getDomainNameById']),
            new TwigFunction('domainIcon', [$this, 'getDomainIconHtml'], ['is_safe' => ['html']]),
            new TwigFunction('isMultidomain', [$this, 'isMultidomain']),
        ];
    }

    /**
     * @return \Shopsys\FrameworkBundle\Component\Domain\Domain
     */
    public function getDomain(): \Shopsys\FrameworkBundle\Component\Domain\Domain
    {
        return $this->domain;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return 'domain';
    }

    /**
     * @param int $domainId
     * @return string
     */
    public function getDomainNameById($domainId): string
    {
        return $this->getDomain()->getDomainConfigById($domainId)->getName();
    }

    /**
     * @param int $domainId
     * @param string $size
     * @return string
     */
    public function getDomainIconHtml($domainId, $size = 'normal'): string
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
    public function isMultidomain(): bool
    {
        return $this->getDomain()->isMultidomain();
    }
}
