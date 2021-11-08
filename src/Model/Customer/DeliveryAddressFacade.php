<?php

declare(strict_types=1);

namespace App\Model\Customer;

use Shopsys\FrameworkBundle\Model\Customer\Customer;
use Shopsys\FrameworkBundle\Model\Customer\DeliveryAddressFacade as BaseDeliveryAddressFacade;

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
}
