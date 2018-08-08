<?php

namespace Shopsys\FrameworkBundle\Model\PersonalData;

interface PersonalDataAccessRequestDataFactoryInterface
{
    public function createForExport(): PersonalDataAccessRequestData;

    public function createForDisplay(): PersonalDataAccessRequestData;
}
