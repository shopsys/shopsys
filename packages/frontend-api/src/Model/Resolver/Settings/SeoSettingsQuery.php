<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Resolver\Settings;

use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\Seo\SeoSettingFacade;
use Shopsys\FrontendApiBundle\Model\Resolver\AbstractQuery;
use Shopsys\FrontendApiBundle\Model\Seo\OrganizationApiFacade;

class SeoSettingsQuery extends AbstractQuery
{
    public function __construct(
        protected readonly Domain $domain,
        protected readonly SeoSettingFacade $seoSettingFacade,
        protected readonly OrganizationApiFacade $organizationApiFacade,
    ) {
    }

    /**
     * @return array{robotsTxtContent: string|null, titleAddOn: string|null, organization: \Shopsys\FrontendApiBundle\Model\Seo\OrganizationQueryDto}
     */
    public function seoSettingsQuery(): array
    {
        $domainId = $this->domain->getId();

        return [
            'organization' => $this->organizationApiFacade->getOrganization($domainId),
            'robotsTxtContent' => $this->seoSettingFacade->getRobotsTxtContent($domainId),
            'titleAddOn' => $this->seoSettingFacade->getTitleAddOn($domainId),
        ];
    }
}
