<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\SalesRepresentative;

use Shopsys\FrameworkBundle\Component\FileUpload\ImageUploadDataFactory;

class SalesRepresentativeDataFactory
{
    public function __construct(
        protected readonly ImageUploadDataFactory $imageUploadDataFactory,
    ) {
    }

    protected function createInstance(): SalesRepresentativeData
    {
        return new SalesRepresentativeData();
    }

    public function create(): SalesRepresentativeData
    {
        $salesRepresentativeData = $this->createInstance();
        $this->fillNew($salesRepresentativeData);

        return $salesRepresentativeData;
    }

    protected function fillNew(SalesRepresentativeData $salesRepresentativeData): void
    {
        $salesRepresentativeData->image = $this->imageUploadDataFactory->create();
    }

    public function createFromSalesRepresentative(SalesRepresentative $salesRepresentative): SalesRepresentativeData
    {
        $salesRepresentativeData = $this->createInstance();

        $salesRepresentativeData->firstName = $salesRepresentative->getFirstName();
        $salesRepresentativeData->lastName = $salesRepresentative->getLastName();
        $salesRepresentativeData->email = $salesRepresentative->getEmail();
        $salesRepresentativeData->telephone = $salesRepresentative->getTelephone();

        $salesRepresentativeData->image = $this->imageUploadDataFactory->createFromEntityAndType($salesRepresentative);

        return $salesRepresentativeData;
    }
}
