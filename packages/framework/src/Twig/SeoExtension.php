<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Twig;

use Override;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\Seo\SeoSettingFacade;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class SeoExtension extends AbstractExtension
{
    public function __construct(
        protected readonly SeoSettingFacade $seoSettingFacade,
        protected readonly Domain $domain,
    ) {
    }

    /**
     * @return \Twig\TwigFunction[]
     */
    #[Override]
    public function getFunctions(): array
    {
        return [
            new TwigFunction('getSeoTitleAddOn', $this->getSeoTitleAddOn(...)),
            new TwigFunction('getSeoMetaDescription', $this->getSeoMetaDescription(...)),
        ];
    }

    public function getName(): string
    {
        return 'seo';
    }

    public function getSeoTitleAddOn(): string
    {
        $currentDomainId = $this->domain->getId();

        return $this->seoSettingFacade->getTitleAddOn($currentDomainId);
    }

    public function getSeoMetaDescription(): string
    {
        $currentDomainId = $this->domain->getId();

        return $this->seoSettingFacade->getDescriptionMainPage($currentDomainId);
    }
}
