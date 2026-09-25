<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Seo;

use Shopsys\FrameworkBundle\Model\Seo\Organization;

class OrganizationQueryDtoFactory
{
    protected function createInstance(): OrganizationQueryDto
    {
        return new OrganizationQueryDto();
    }

    /**
     * @param string[] $socialNetworkUrls
     */
    public function create(?Organization $organization, ?string $logo, array $socialNetworkUrls): OrganizationQueryDto
    {
        $organizationQueryDto = $this->createInstance();
        $organizationQueryDto->logo = $logo;
        $organizationQueryDto->socialNetworkUrls = $socialNetworkUrls;

        if ($organization !== null) {
            $this->fillFromOrganization($organizationQueryDto, $organization);
        }

        return $organizationQueryDto;
    }

    protected function fillFromOrganization(
        OrganizationQueryDto $organizationQueryDto,
        Organization $organization,
    ): void {
        $organizationQueryDto->name = $organization->getName();
        $organizationQueryDto->companyTaxNumber = $organization->getCompanyTaxNumber();
        $organizationQueryDto->companyNumber = $organization->getCompanyNumber();
        $organizationQueryDto->description = $organization->getDescription();
        $organizationQueryDto->street = $organization->getStreet();
        $organizationQueryDto->city = $organization->getCity();
        $organizationQueryDto->postcode = $organization->getPostcode();
        $organizationQueryDto->country = $organization->getCountry();
    }
}
