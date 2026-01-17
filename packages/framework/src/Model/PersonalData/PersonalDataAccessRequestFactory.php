<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\PersonalData;

use Shopsys\FrameworkBundle\Component\EntityExtension\EntityNameResolver;

class PersonalDataAccessRequestFactory
{
    public function __construct(protected readonly EntityNameResolver $entityNameResolver)
    {
    }

    public function create(PersonalDataAccessRequestData $data): PersonalDataAccessRequest
    {
        $entityClassName = $this->entityNameResolver->resolve(PersonalDataAccessRequest::class);

        return new $entityClassName($data);
    }
}
