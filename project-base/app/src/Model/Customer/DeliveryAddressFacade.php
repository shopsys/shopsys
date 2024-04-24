<?php

declare(strict_types=1);

namespace App\Model\Customer;

use InvalidArgumentException;
use Shopsys\FrameworkBundle\Model\Customer\Customer;
use Shopsys\FrameworkBundle\Model\Customer\DeliveryAddressFacade as BaseDeliveryAddressFacade;

/**
 * @property \App\Model\Customer\DeliveryAddressRepository $deliveryAddressRepository
 * @method edit(int $deliveryAddressId, \App\Model\Customer\DeliveryAddressData $deliveryAddressData)
 * @method \App\Model\Customer\DeliveryAddress|null createIfAddressFilled(\App\Model\Customer\DeliveryAddressData $deliveryAddressData)
 * @method \App\Model\Customer\DeliveryAddress delete(int $deliveryAddressId)
 * @method \App\Model\Customer\DeliveryAddress getById(int $deliveryAddressId)
 * @method __construct(\Shopsys\FrameworkBundle\Model\Customer\DeliveryAddressFactory $deliveryAddressFactory, \App\Model\Customer\DeliveryAddressRepository $deliveryAddressRepository, \Doctrine\ORM\EntityManagerInterface $em)
 * @method \App\Model\Customer\DeliveryAddress|null findByUuidAndCustomer(string $uuid, \Shopsys\FrameworkBundle\Model\Customer\Customer $customer)
 */
class DeliveryAddressFacade extends BaseDeliveryAddressFacade
{
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
     * @param \App\Model\Customer\DeliveryAddressData $deliveryAddressData
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
     * @return \App\Model\Customer\DeliveryAddress
     */
    public function getByUuidAndCustomer(string $uuid, Customer $customer): DeliveryAddress
    {
        return $this->deliveryAddressRepository->getByUuidAndCustomer($uuid, $customer);
    }
}
