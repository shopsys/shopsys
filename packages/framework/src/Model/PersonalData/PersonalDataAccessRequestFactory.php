<?php

namespace Shopsys\FrameworkBundle\Model\PersonalData;

use Shopsys\FrameworkBundle\Component\EntityExtension\EntityNameResolver;

class PersonalDataAccessRequestFactory implements PersonalDataAccessRequestFactoryInterface
{
    /**
     * @param \Shopsys\FrameworkBundle\Component\EntityExtension\EntityNameResolver $entityNameResolver
     */
    public function __construct(protected readonly EntityNameResolver $entityNameResolver)
    {
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\PersonalData\PersonalDataAccessRequestData $data
     * @return \Shopsys\FrameworkBundle\Model\PersonalData\PersonalDataAccessRequest
     */
    public function create(PersonalDataAccessRequestData $data): PersonalDataAccessRequest
    {
        $classData = $this->entityNameResolver->resolve(PersonalDataAccessRequest::class);

        return new $classData($data);
    }
}
