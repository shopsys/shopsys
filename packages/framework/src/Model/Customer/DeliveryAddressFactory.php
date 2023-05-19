<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Customer;

use Shopsys\FrameworkBundle\Component\EntityExtension\EntityNameResolver;

class DeliveryAddressFactory implements DeliveryAddressFactoryInterface
{
    /**
     * @param \Shopsys\FrameworkBundle\Component\EntityExtension\EntityNameResolver $entityNameResolver
     */
    public function __construct(protected readonly EntityNameResolver $entityNameResolver)
    {
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Customer\DeliveryAddressData $data
     * @return \Shopsys\FrameworkBundle\Model\Customer\DeliveryAddress|null
     */
    public function create(DeliveryAddressData $data): ?DeliveryAddress
    {
        if (!$data->addressFilled) {
            return null;
        }

        $classData = $this->entityNameResolver->resolve(DeliveryAddress::class);

        return new $classData($data);
    }
}
