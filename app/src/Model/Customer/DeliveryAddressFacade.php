<?php

declare(strict_types=1);

namespace App\Model\Customer;

use Shopsys\FrameworkBundle\Model\Customer\Customer;
use Shopsys\FrameworkBundle\Model\Customer\DeliveryAddressFacade as BaseDeliveryAddressFacade;
use Shopsys\FrameworkBundle\Model\Customer\Exception\DeliveryAddressNotFoundException;

/**
 * @property \App\Model\Customer\DeliveryAddressRepository $deliveryAddressRepository
 * @method edit(int $deliveryAddressId, \App\Model\Customer\DeliveryAddressData $deliveryAddressData)
 * @method \App\Model\Customer\DeliveryAddress create(\App\Model\Customer\DeliveryAddressData $deliveryAddressData)
 * @method \App\Model\Customer\DeliveryAddress delete(int $deliveryAddressId)
 * @method \App\Model\Customer\DeliveryAddress getById(int $deliveryAddressId)
 * @method __construct(\Shopsys\FrameworkBundle\Model\Customer\DeliveryAddressFactory $deliveryAddressFactory, \App\Model\Customer\DeliveryAddressRepository $deliveryAddressRepository, \Doctrine\ORM\EntityManagerInterface $em)
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
        $deliveryAddress = $this->deliveryAddressRepository->findByUuidAndCustomer($deliveryAddressData->uuid, $customer);

        if ($deliveryAddress === null) {
            throw new DeliveryAddressNotFoundException(
                'Delivery address with UUID ' . $deliveryAddressData->uuid . ' not found.'
            );
        }

        $this->edit($deliveryAddress->getId(), $deliveryAddressData);
    }
}
