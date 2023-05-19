<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Twig;

use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\Seo\SeoSettingFacade;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class SeoExtension extends AbstractExtension
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Seo\SeoSettingFacade $seoSettingFacade
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     */
    public function __construct(
        protected readonly SeoSettingFacade $seoSettingFacade,
        protected readonly Domain $domain,
    ) {
    }

    /**
     * @return \Twig\TwigFunction[]
     */
    public function getFunctions()
    {
        return [
            new TwigFunction('getSeoTitleAddOn', [$this, 'getSeoTitleAddOn']),
            new TwigFunction('getSeoMetaDescription', [$this, 'getSeoMetaDescription']),
        ];
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'seo';
    }

    /**
     * @return string
     */
    public function getSeoTitleAddOn()
    {
        $currentDomainId = $this->domain->getId();

        return $this->seoSettingFacade->getTitleAddOn($currentDomainId);
    }

    /**
     * @return string
     */
    public function getSeoMetaDescription()
    {
        $currentDomainId = $this->domain->getId();

        return $this->seoSettingFacade->getDescriptionMainPage($currentDomainId);
    }
}
