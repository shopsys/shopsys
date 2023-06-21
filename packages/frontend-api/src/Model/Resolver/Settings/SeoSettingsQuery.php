<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Resolver\Settings;

use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\Seo\SeoSettingFacade;
use Shopsys\FrontendApiBundle\Model\Resolver\AbstractQuery;

class SeoSettingsQuery extends AbstractQuery
{
    /**
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     * @param \Shopsys\FrameworkBundle\Model\Seo\SeoSettingFacade $seoSettingFacade
     */
    public function __construct(
        protected readonly Domain $domain,
        protected readonly SeoSettingFacade $seoSettingFacade,
    ) {
    }

    /**
     * @return array{robotsTxtContent: string, title: string, titleAddOn: string, metaDescription: string}
     */
    public function seoSettingsQuery(): array
    {
        return [
            'robotsTxtContent' => $this->seoSettingFacade->getRobotsTxtContent($this->domain->getId()),
            'title' => $this->seoSettingFacade->getTitleMainPage($this->domain->getId()),
            'titleAddOn' => $this->seoSettingFacade->getTitleAddOn($this->domain->getId()),
            'metaDescription' => $this->seoSettingFacade->getDescriptionMainPage($this->domain->getId()),
        ];
    }
}
