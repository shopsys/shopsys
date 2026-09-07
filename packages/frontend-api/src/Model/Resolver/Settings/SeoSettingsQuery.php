<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Resolver\Settings;

use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\Seo\OrganizationSettingFacade;
use Shopsys\FrameworkBundle\Model\Seo\SeoSettingFacade;
use Shopsys\FrontendApiBundle\Model\Resolver\AbstractQuery;

class SeoSettingsQuery extends AbstractQuery
{
    public function __construct(
        protected readonly Domain $domain,
        protected readonly SeoSettingFacade $seoSettingFacade,
        protected readonly OrganizationSettingFacade $organizationSettingFacade,
    ) {
    }

    /**
     * @return array{robotsTxtContent: string|null, title: string|null, titleAddOn: string|null, metaDescription: string|null, organization: array<string, string|array<string>|null>}
     */
    public function seoSettingsQuery(): array
    {
        return [
            'organization' => $this->organizationSettingFacade->getOrganization($this->domain->getId()),
            'robotsTxtContent' => $this->seoSettingFacade->getRobotsTxtContent($this->domain->getId()),
            'title' => $this->seoSettingFacade->getTitleMainPage($this->domain->getId()),
            'titleAddOn' => $this->seoSettingFacade->getTitleAddOn($this->domain->getId()),
            'metaDescription' => $this->seoSettingFacade->getDescriptionMainPage($this->domain->getId()),
        ];
    }
}
