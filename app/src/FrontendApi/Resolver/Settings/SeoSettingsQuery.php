<?php

declare(strict_types=1);

namespace App\FrontendApi\Resolver\Settings;

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
        private readonly Domain $domain,
        private readonly SeoSettingFacade $seoSettingFacade
    ) {
    }

    /**
     * @return array{title: string, titleAddOn: string, metaDescription: string}
     */
    public function seoSettingsQuery(): array
    {
        return [
            'title' => $this->seoSettingFacade->getTitleMainPage($this->domain->getId()),
            'titleAddOn' => $this->seoSettingFacade->getTitleAddOn($this->domain->getId()),
            'metaDescription' => $this->seoSettingFacade->getDescriptionMainPage($this->domain->getId()),
        ];
    }
}
