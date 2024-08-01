<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\SalesRepresentative;

use Shopsys\FrameworkBundle\Component\FileUpload\ImageUploadDataFactory;

class SalesRepresentativeDataFactory implements SalesRepresentativeDataFactoryInterface
{
    /**
     * @param \Shopsys\FrameworkBundle\Component\FileUpload\ImageUploadDataFactory $imageUploadDataFactory
     */
    public function __construct(
        protected readonly ImageUploadDataFactory $imageUploadDataFactory,
    ) {
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\SalesRepresentative\SalesRepresentativeData
     */
    protected function createInstance(): SalesRepresentativeData
    {
        return new SalesRepresentativeData();
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\SalesRepresentative\SalesRepresentativeData
     */
    public function create(): SalesRepresentativeData
    {
        $salesRepresentativeData = $this->createInstance();
        $this->fillNew($salesRepresentativeData);

        return $salesRepresentativeData;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\SalesRepresentative\SalesRepresentativeData $salesRepresentativeData
     */
    protected function fillNew(SalesRepresentativeData $salesRepresentativeData): void
    {
        $salesRepresentativeData->image = $this->imageUploadDataFactory->create();
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\SalesRepresentative\SalesRepresentative $salesRepresentative
     * @return \Shopsys\FrameworkBundle\Model\SalesRepresentative\SalesRepresentativeData
     */
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
