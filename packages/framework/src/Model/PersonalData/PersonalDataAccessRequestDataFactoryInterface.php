<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\PersonalData;

interface PersonalDataAccessRequestDataFactoryInterface
{
    /**
     * @return \Shopsys\FrameworkBundle\Model\PersonalData\PersonalDataAccessRequestData
     */
    public function createForExport(): PersonalDataAccessRequestData;

    /**
     * @return \Shopsys\FrameworkBundle\Model\PersonalData\PersonalDataAccessRequestData
     */
    public function createForDisplay(): PersonalDataAccessRequestData;
}
