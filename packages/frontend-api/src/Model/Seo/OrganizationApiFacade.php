<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Seo;

use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Image\Exception\ImageNotFoundException;
use Shopsys\FrameworkBundle\Component\Image\ImageFacade;
use Shopsys\FrameworkBundle\Model\Mail\Setting\MailSettingFacade;
use Shopsys\FrameworkBundle\Model\Seo\Organization;
use Shopsys\FrameworkBundle\Model\Seo\OrganizationFacade;

class OrganizationApiFacade
{
    public function __construct(
        protected readonly Domain $domain,
        protected readonly OrganizationFacade $organizationFacade,
        protected readonly ImageFacade $imageFacade,
        protected readonly MailSettingFacade $mailSettingFacade,
        protected readonly OrganizationQueryDtoFactory $organizationQueryDtoFactory,
    ) {
    }

    public function getOrganization(int $domainId): OrganizationQueryDto
    {
        $organization = $this->organizationFacade->findByDomainId($domainId);

        return $this->organizationQueryDtoFactory->create(
            $organization,
            $organization === null ? null : $this->findLogoUrl($organization, $domainId),
            array_values(array_filter($this->mailSettingFacade->getFooterIconUrls($domainId))),
        );
    }

    protected function findLogoUrl(Organization $organization, int $domainId): ?string
    {
        try {
            return $this->imageFacade->getImageUrl($this->domain->getDomainConfigById($domainId), $organization);
        } catch (ImageNotFoundException) {
            return null;
        }
    }
}
