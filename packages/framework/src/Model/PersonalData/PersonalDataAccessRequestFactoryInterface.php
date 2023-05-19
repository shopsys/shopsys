<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\PersonalData;

interface PersonalDataAccessRequestFactoryInterface
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\PersonalData\PersonalDataAccessRequestData $data
     * @return \Shopsys\FrameworkBundle\Model\PersonalData\PersonalDataAccessRequest
     */
    public function create(PersonalDataAccessRequestData $data): PersonalDataAccessRequest;
}
