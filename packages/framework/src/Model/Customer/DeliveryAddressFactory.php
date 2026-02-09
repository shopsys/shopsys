<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Customer;

use Shopsys\FrameworkBundle\Component\EntityExtension\EntityNameResolver;

class DeliveryAddressFactory
{
    public function __construct(protected readonly EntityNameResolver $entityNameResolver)
    {
    }

    public function create(DeliveryAddressData $data): ?DeliveryAddress
    {
        if (!$data->addressFilled) {
            return null;
        }

        $entityClassName = $this->entityNameResolver->resolve(DeliveryAddress::class);

        return new $entityClassName($data);
    }
}
