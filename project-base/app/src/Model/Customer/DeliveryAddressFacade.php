<?php

declare(strict_types=1);

namespace App\Model\Customer;

use Shopsys\FrameworkBundle\Model\Customer\DeliveryAddressFacade as BaseDeliveryAddressFacade;

/**
 * @property \App\Model\Customer\DeliveryAddressRepository $deliveryAddressRepository
 * @method edit(int $deliveryAddressId, \App\Model\Customer\DeliveryAddressData $deliveryAddressData)
 * @method \App\Model\Customer\DeliveryAddress|null createIfAddressFilled(\App\Model\Customer\DeliveryAddressData $deliveryAddressData)
 * @method \App\Model\Customer\DeliveryAddress delete(int $deliveryAddressId)
 * @method \App\Model\Customer\DeliveryAddress getById(int $deliveryAddressId)
 * @method __construct(\Shopsys\FrameworkBundle\Model\Customer\DeliveryAddressFactory $deliveryAddressFactory, \App\Model\Customer\DeliveryAddressRepository $deliveryAddressRepository, \Doctrine\ORM\EntityManagerInterface $em)
 * @method \App\Model\Customer\DeliveryAddress|null findByUuidAndCustomer(string $uuid, \Shopsys\FrameworkBundle\Model\Customer\Customer $customer)
 * @method editByCustomer(\Shopsys\FrameworkBundle\Model\Customer\Customer $customer, \App\Model\Customer\DeliveryAddressData $deliveryAddressData)
 * @method \App\Model\Customer\DeliveryAddress getByUuidAndCustomer(string $uuid, \Shopsys\FrameworkBundle\Model\Customer\Customer $customer)
 */
class DeliveryAddressFacade extends BaseDeliveryAddressFacade
{
}
