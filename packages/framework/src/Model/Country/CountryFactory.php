<?php

namespace Shopsys\FrameworkBundle\Model\Country;

use Shopsys\FrameworkBundle\Component\EntityExtension\EntityNameResolver;

class CountryFactory implements CountryFactoryInterface
{
    /**
     * @param \Shopsys\FrameworkBundle\Component\EntityExtension\EntityNameResolver $entityNameResolver
     */
    public function __construct(protected readonly EntityNameResolver $entityNameResolver)
    {
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Country\CountryData $data
     * @return \Shopsys\FrameworkBundle\Model\Country\Country
     */
    public function create(CountryData $data): Country
    {
        $classData = $this->entityNameResolver->resolve(Country::class);

        return new $classData($data);
    }
}
