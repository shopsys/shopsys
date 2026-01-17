<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Customer;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Shopsys\FrameworkBundle\Model\Customer\Exception\DeliveryAddressNotFoundException;

class DeliveryAddressRepository
{
    protected EntityManagerInterface $em;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->em = $entityManager;
    }

    protected function getDeliveryAddressRepository(): EntityRepository
    {
        return $this->em->getRepository(DeliveryAddress::class);
    }

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

    public function findByUuidAndCustomer(string $uuid, Customer $customer): ?DeliveryAddress
    {
        return $this->getDeliveryAddressRepository()->findOneBy(
            [
                'uuid' => $uuid,
                'customer' => $customer,
            ],
        );
    }

    public function getByUuidAndCustomer(string $uuid, Customer $customer): DeliveryAddress
    {
        $deliveryAddress = $this->findByUuidAndCustomer($uuid, $customer);

        if ($deliveryAddress === null) {
            throw new DeliveryAddressNotFoundException(
                'Delivery address with UUID ' . $uuid . ' not found.',
            );
        }

        return $deliveryAddress;
    }
}
