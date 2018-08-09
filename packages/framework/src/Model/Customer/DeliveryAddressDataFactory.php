<?php

namespace Shopsys\FrameworkBundle\Model\Customer;

class DeliveryAddressDataFactory implements DeliveryAddressDataFactoryInterface
{
    public function create(): DeliveryAddressData
    {
        return new DeliveryAddressData();
    }

    public function createFromDeliveryAddress(DeliveryAddress $deliveryAddress): DeliveryAddressData
    {
        $deliveryAddressData = new DeliveryAddressData();
        $this->fillFromDeliveryAddress($deliveryAddressData, $deliveryAddress);

        return $deliveryAddressData;
    }

    protected function fillFromDeliveryAddress(DeliveryAddressData $deliveryAddressData, DeliveryAddress $deliveryAddress)
    {
        $deliveryAddressData->addressFilled = true;
        $deliveryAddressData->companyName = $deliveryAddress->getCompanyName();
        $deliveryAddressData->firstName = $deliveryAddress->getFirstName();
        $deliveryAddressData->lastName = $deliveryAddress->getLastName();
        $deliveryAddressData->telephone = $deliveryAddress->getTelephone();
        $deliveryAddressData->street = $deliveryAddress->getStreet();
        $deliveryAddressData->city = $deliveryAddress->getCity();
        $deliveryAddressData->postcode = $deliveryAddress->getPostcode();
        $deliveryAddressData->country = $deliveryAddress->getCountry();
    }
}
