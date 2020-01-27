<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Customer;

use Doctrine\ORM\EntityManagerInterface;

class DeliveryAddressFacade
{
    /**
     * @var \Shopsys\FrameworkBundle\Model\Customer\DeliveryAddressFactory
     */
    protected $deliveryAddressFactory;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Customer\DeliveryAddressRepository
     */
    protected $deliveryAddressRepository;

    /**
     * @var \Doctrine\ORM\EntityManagerInterface
     */
    protected $em;

    /**
     * @param \Shopsys\FrameworkBundle\Model\Customer\DeliveryAddressFactory $deliveryAddressFactory
     * @param \Shopsys\FrameworkBundle\Model\Customer\DeliveryAddressRepository $deliveryAddressRepository
     * @param \Doctrine\ORM\EntityManagerInterface $em
     */
    public function __construct(
        DeliveryAddressFactory $deliveryAddressFactory,
        DeliveryAddressRepository $deliveryAddressRepository,
        EntityManagerInterface $em
    ) {
        $this->deliveryAddressFactory = $deliveryAddressFactory;
        $this->deliveryAddressRepository = $deliveryAddressRepository;
        $this->em = $em;
    }

    /**
     * @param int $deliveryAddressId
     * @param \Shopsys\FrameworkBundle\Model\Customer\DeliveryAddressData $deliveryAddressData
     */
    public function edit(int $deliveryAddressId, DeliveryAddressData $deliveryAddressData): void
    {
        $deliveryAddress = $this->deliveryAddressRepository->getById($deliveryAddressId);
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
}
