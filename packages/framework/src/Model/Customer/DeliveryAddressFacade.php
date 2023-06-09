<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Customer;

use Doctrine\ORM\EntityManagerInterface;

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
     * @return \Shopsys\FrameworkBundle\Model\Customer\DeliveryAddress
     */
    public function create(DeliveryAddressData $deliveryAddressData): DeliveryAddress
    {
        $deliveryAddress = $this->deliveryAddressFactory->create($deliveryAddressData);

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
}
