<?php

namespace Shopsys\FrameworkBundle\Model\PersonalData;

class PersonalDataAccessRequestFactory implements PersonalDataAccessRequestFactoryInterface
{
    public function create(PersonalDataAccessRequestData $data): PersonalDataAccessRequest
    {
        return new PersonalDataAccessRequest($data);
    }
}
