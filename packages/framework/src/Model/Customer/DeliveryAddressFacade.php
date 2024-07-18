<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Customer;

use Doctrine\ORM\EntityManagerInterface;
use InvalidArgumentException;

class DeliveryAddressFacade
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Customer\DeliveryAddressFactory $deliveryAddressFactory
     * @param \Shopsys\FrameworkBundle\Model\Customer\DeliveryAddressRepository $deliveryAddressRepository
     * @param \Doctrine\ORM\EntityManagerInterface $em
     */
    public function __construct(
        protected readonly DeliveryAddressFactory $deliveryAddressFactory,
        protected readonly DeliveryAddressRepository $deliveryAddressRepository,
        protected readonly EntityManagerInterface $em,
    ) {
    }

    /**
     * @param int $deliveryAddressId
     * @param \Shopsys\FrameworkBundle\Model\Customer\DeliveryAddressData $deliveryAddressData
     */
    public function edit(int $deliveryAddressId, DeliveryAddressData $deliveryAddressData): void
    {
        $deliveryAddress = $this->getById($deliveryAddressId);
        $deliveryAddress->edit($deliveryAddressData);

        $this->em->flush();
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Customer\DeliveryAddressData $deliveryAddressData
     * @return \Shopsys\FrameworkBundle\Model\Customer\DeliveryAddress|null
     */
    public function createIfAddressFilled(DeliveryAddressData $deliveryAddressData): ?DeliveryAddress
    {
        $deliveryAddress = $this->deliveryAddressFactory->create($deliveryAddressData);

        if ($deliveryAddress === null) {
            return null;
        }

        $this->em->persist($deliveryAddress);
        $this->em->flush();

        return $deliveryAddress;
    }

    /**
     * @param int $deliveryAddressId
     * @return \Shopsys\FrameworkBundle\Model\Customer\DeliveryAddress
     */
    public function delete(int $deliveryAddressId): DeliveryAddress
    {
        $deliveryAddress = $this->deliveryAddressRepository->getById($deliveryAddressId);

        $this->em->remove($deliveryAddress);
        $this->em->flush();

        return $deliveryAddress;
    }

    /**
     * @param int $deliveryAddressId
     * @return \Shopsys\FrameworkBundle\Model\Customer\DeliveryAddress
     */
    public function getById(int $deliveryAddressId): DeliveryAddress
    {
        return $this->deliveryAddressRepository->getById($deliveryAddressId);
    }

    /**
     * @param string $uuid
     * @param \Shopsys\FrameworkBundle\Model\Customer\Customer $customer
     * @return \Shopsys\FrameworkBundle\Model\Customer\DeliveryAddress|null
     */
    public function findByUuidAndCustomer(string $uuid, Customer $customer): ?DeliveryAddress
    {
        return $this->deliveryAddressRepository->findByUuidAndCustomer($uuid, $customer);
    }

    /**
     * @param string $uuid
     * @param \Shopsys\FrameworkBundle\Model\Customer\Customer $customer
     */
    public function deleteByUuidAndCustomer(string $uuid, Customer $customer): void
    {
        $deliveryAddress = $this->deliveryAddressRepository->findByUuidAndCustomer($uuid, $customer);

        if (!$deliveryAddress) {
            return;
        }

        $this->em->remove($deliveryAddress);
        $this->em->flush();
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Customer\Customer $customer
     * @param \Shopsys\FrameworkBundle\Model\Customer\DeliveryAddressData $deliveryAddressData
     */
    public function editByCustomer(Customer $customer, DeliveryAddressData $deliveryAddressData): void
    {
        if ($deliveryAddressData->uuid === null) {
            throw new InvalidArgumentException('UUID is missing in DeliveryAddressData');
        }

        $deliveryAddress = $this->getByUuidAndCustomer($deliveryAddressData->uuid, $customer);

        $this->edit($deliveryAddress->getId(), $deliveryAddressData);
    }

    /**
     * @param string $uuid
     * @param \Shopsys\FrameworkBundle\Model\Customer\Customer $customer
     * @return \Shopsys\FrameworkBundle\Model\Customer\DeliveryAddress
     */
    public function getByUuidAndCustomer(string $uuid, Customer $customer): DeliveryAddress
    {
        return $this->deliveryAddressRepository->getByUuidAndCustomer($uuid, $customer);
    }
}
