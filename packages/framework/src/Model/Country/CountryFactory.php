<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Country;

use Shopsys\FrameworkBundle\Component\EntityExtension\EntityNameResolver;

class CountryFactory
{
    public function __construct(protected readonly EntityNameResolver $entityNameResolver)
    {
    }

    public function create(CountryData $data): Country
    {
        $entityClassName = $this->entityNameResolver->resolve(Country::class);

        return new $entityClassName($data);
    }
}
