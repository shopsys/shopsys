<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Customer;

use Shopsys\FrameworkBundle\Component\EntityExtension\EntityNameResolver;

class BillingAddressFactory
{
    public function __construct(protected readonly EntityNameResolver $entityNameResolver)
    {
    }

    public function create(BillingAddressData $data): BillingAddress
    {
        $entityClassName = $this->entityNameResolver->resolve(BillingAddress::class);

        return new $entityClassName($data);
    }
}
