<?php

namespace Shopsys\FrameworkBundle\Twig;

use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Domain\DomainFacade;
use Symfony\Component\Asset\Packages;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Twig_SimpleFunction;

class DomainExtension extends \Twig_Extension
{
    /**
     * @var string
     */
    private $domainImagesUrlPrefix;

    /**
     * @var \Symfony\Component\DependencyInjection\ContainerInterface
     */
    private $container;

    /**
     * @var \Symfony\Component\Asset\Packages
     */
    private $assetPackages;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Domain\Domain
     */
    private $domain;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Domain\DomainFacade
     */
    private $domainFacade;

    public function __construct(
        $domainImagesUrlPrefix,
        ContainerInterface $container,
        Packages $assetPackages,
        Domain $domain,
        DomainFacade $domainFacade
    ) {
        $this->domainImagesUrlPrefix = $domainImagesUrlPrefix;
        $this->container = $container;
        $this->assetPackages = $assetPackages;
        $this->domain = $domain;
        $this->domainFacade = $domainFacade;
    }

    public function getFunctions()
    {
        return [
            new Twig_SimpleFunction('getDomain', [$this, 'getDomain']),
            new Twig_SimpleFunction('getDomainName', [$this, 'getDomainNameById']),
            new Twig_SimpleFunction('domainIcon', [$this, 'getDomainIconHtml'], ['is_safe' => ['html']]),
            new Twig_SimpleFunction('isMultidomain', [$this, 'isMultidomain']),
        ];
    }

    public function getDomain(): \Shopsys\FrameworkBundle\Component\Domain\Domain
    {
        return $this->domain;
    }

    public function getName(): string
    {
        return 'domain';
    }

    public function getDomainNameById(int $domainId): string
    {
        return $this->getDomain()->getDomainConfigById($domainId)->getName();
    }

    public function getDomainIconHtml(int $domainId, string $size = 'normal'): string
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
                        . '" alt="' . htmlspecialchars($domainId, ENT_QUOTES) . '"'
                        . ' title="' . htmlspecialchars($domainName, ENT_QUOTES) . '"/>
                    </span>
                </span>';
        } else {
            return '
                <span class="in-image in-image--' . $size . '">
                    <span
                        class="in-image__in in-image__in--' . $domainId . '"
                        title="' . htmlspecialchars($domainName, ENT_QUOTES) . '"
                    >' . $domainId . '</span>
                </span>
            ';
        }
    }

    public function isMultidomain(): bool
    {
        return $this->getDomain()->isMultidomain();
    }
}
