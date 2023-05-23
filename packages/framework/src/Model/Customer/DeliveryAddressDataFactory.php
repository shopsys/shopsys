<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Customer;

class DeliveryAddressDataFactory implements DeliveryAddressDataFactoryInterface
{
    /**
     * @return \Shopsys\FrameworkBundle\Model\Customer\DeliveryAddressData
     */
    protected function createInstance(): DeliveryAddressData
    {
        return new DeliveryAddressData();
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Customer\DeliveryAddressData
     */
    public function create(): DeliveryAddressData
    {
        return $this->createInstance();
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Customer\DeliveryAddress $deliveryAddress
     * @return \Shopsys\FrameworkBundle\Model\Customer\DeliveryAddressData
     */
    public function createFromDeliveryAddress(DeliveryAddress $deliveryAddress): DeliveryAddressData
    {
        $deliveryAddressData = $this->createInstance();
        $this->fillFromDeliveryAddress($deliveryAddressData, $deliveryAddress);

        return $deliveryAddressData;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Customer\DeliveryAddressData $deliveryAddressData
     * @param \Shopsys\FrameworkBundle\Model\Customer\DeliveryAddress $deliveryAddress
     */
    protected function fillFromDeliveryAddress(
        DeliveryAddressData $deliveryAddressData,
        DeliveryAddress $deliveryAddress,
    ) {
        $deliveryAddressData->companyName = $deliveryAddress->getCompanyName();
        $deliveryAddressData->firstName = $deliveryAddress->getFirstName();
        $deliveryAddressData->lastName = $deliveryAddress->getLastName();
        $deliveryAddressData->telephone = $deliveryAddress->getTelephone();
        $deliveryAddressData->street = $deliveryAddress->getStreet();
        $deliveryAddressData->city = $deliveryAddress->getCity();
        $deliveryAddressData->postcode = $deliveryAddress->getPostcode();
        $deliveryAddressData->country = $deliveryAddress->getCountry();
        $deliveryAddressData->customer = $deliveryAddress->getCustomer();
    }
}
