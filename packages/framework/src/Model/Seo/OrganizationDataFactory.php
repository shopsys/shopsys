<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Seo;

use Shopsys\FrameworkBundle\Component\FileUpload\ImageUploadDataFactory;

class OrganizationDataFactory
{
    public function __construct(protected readonly ImageUploadDataFactory $imageUploadDataFactory)
    {
    }

    public function create(): OrganizationData
    {
        $data = $this->createInstance();
        $data->image = $this->imageUploadDataFactory->create();

        return $data;
    }

    public function createFromOrganization(Organization $organization): OrganizationData
    {
        $data = $this->createInstance();
        $data->name = $organization->getName();
        $data->companyTaxNumber = $organization->getCompanyTaxNumber();
        $data->companyNumber = $organization->getCompanyNumber();
        $data->description = $organization->getDescription();
        $data->street = $organization->getStreet();
        $data->city = $organization->getCity();
        $data->postcode = $organization->getPostcode();
        $data->country = $organization->getCountry();
        $data->image = $this->imageUploadDataFactory->createFromEntityAndType($organization);

        return $data;
    }

    protected function createInstance(): OrganizationData
    {
        return new OrganizationData();
    }
}
