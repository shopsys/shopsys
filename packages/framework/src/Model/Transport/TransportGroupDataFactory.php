<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Transport;

use Shopsys\FrameworkBundle\Component\FileUpload\ImageUploadDataFactory;

class TransportGroupDataFactory
{
    public function __construct(
        protected readonly ImageUploadDataFactory $imageUploadDataFactory,
    ) {
    }

    protected function createInstance(): TransportGroupData
    {
        return new TransportGroupData();
    }

    public function create(): TransportGroupData
    {
        $transportGroupData = $this->createInstance();
        $this->fillNew($transportGroupData);

        return $transportGroupData;
    }

    public function fillNew(TransportGroupData $transportGroupData): void
    {
        $transportGroupData->position = 0;
        $transportGroupData->image = $this->imageUploadDataFactory->create();
    }

    public function createFromTransportGroup(TransportGroup $transportGroup): TransportGroupData
    {
        $transportGroupData = $this->createInstance();
        $this->fillFromTransportGroup($transportGroupData, $transportGroup);

        return $transportGroupData;
    }

    public function fillFromTransportGroup(
        TransportGroupData $transportGroupData,
        TransportGroup $transportGroup,
    ): void {
        $transportGroupData->name = $transportGroup->getNames();
        $transportGroupData->position = $transportGroup->getPosition();
        $transportGroupData->image = $this->imageUploadDataFactory->createFromEntityAndType($transportGroup);
    }
}
