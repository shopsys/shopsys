<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Seo;

use Shopsys\FrameworkBundle\Model\Mail\Setting\MailSettingFacade;
use Shopsys\FrameworkBundle\Model\Seo\OrganizationFacade;

class OrganizationApiFacade
{
    public function __construct(
        protected readonly OrganizationFacade $organizationFacade,
        protected readonly MailSettingFacade $mailSettingFacade,
        protected readonly OrganizationQueryDtoFactory $organizationQueryDtoFactory,
    ) {
    }

    public function getOrganization(int $domainId): OrganizationQueryDto
    {
        return $this->organizationQueryDtoFactory->create(
            $this->organizationFacade->findByDomainId($domainId),
            array_values(array_filter($this->mailSettingFacade->getFooterIconUrls($domainId))),
        );
    }
}
