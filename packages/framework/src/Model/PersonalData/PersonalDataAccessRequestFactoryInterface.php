<?php

namespace Shopsys\FrameworkBundle\Model\PersonalData;

interface PersonalDataAccessRequestFactoryInterface
{

    public function create(PersonalDataAccessRequestData $data): PersonalDataAccessRequest;
}
