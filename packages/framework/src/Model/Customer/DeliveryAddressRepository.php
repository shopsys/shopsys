<?php

namespace Shopsys\FrameworkBundle\Model\Customer;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Shopsys\FrameworkBundle\Model\Customer\Exception\DeliveryAddressNotFoundException;

class DeliveryAddressRepository
{
    protected EntityManagerInterface $em;

    /**
     * @param \Doctrine\ORM\EntityManagerInterface $entityManager
     */
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->em = $entityManager;
    }

    /**
     * @return \Doctrine\ORM\EntityRepository
     */
    protected function getDeliveryAddressRepository(): EntityRepository
    {
        return $this->em->getRepository(DeliveryAddress::class);
    }

    /**
     * @param int $deliveryAddressId
     * @return \Shopsys\FrameworkBundle\Model\Customer\DeliveryAddress
     */
    public function getById(int $deliveryAddressId): DeliveryAddress
    {
        $deliveryAddress = $this->getDeliveryAddressRepository()->find($deliveryAddressId);

        if ($deliveryAddress === null) {
            throw new DeliveryAddressNotFoundException(
                'Delivery address with ID ' . $deliveryAddressId . ' not found.',
            );
        }

        return $deliveryAddress;
    }
}
