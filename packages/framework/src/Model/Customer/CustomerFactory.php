<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Customer;

use Shopsys\FrameworkBundle\Component\EntityExtension\EntityNameResolver;

class CustomerFactory
{
    public function __construct(protected readonly EntityNameResolver $entityNameResolver)
    {
    }

    public function create(CustomerData $customerData): Customer
    {
        $entityClassName = $this->entityNameResolver->resolve(Customer::class);

        return new $entityClassName($customerData);
    }
}
