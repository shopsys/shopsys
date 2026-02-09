<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\PersonalData;

class PersonalDataAccessRequestDataFactory
{
    protected function createInstance(): PersonalDataAccessRequestData
    {
        return new PersonalDataAccessRequestData();
    }

    public function create(): PersonalDataAccessRequestData
    {
        return $this->createInstance();
    }

    public function createForExport(): PersonalDataAccessRequestData
    {
        $personalDataAccessRequestData = $this->createInstance();
        $personalDataAccessRequestData->type = PersonalDataAccessRequest::TYPE_EXPORT;

        return $personalDataAccessRequestData;
    }

    public function createForDisplay(): PersonalDataAccessRequestData
    {
        $personalDataAccessRequestData = $this->createInstance();
        $personalDataAccessRequestData->type = PersonalDataAccessRequest::TYPE_DISPLAY;

        return $personalDataAccessRequestData;
    }
}
