<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Customer;

class DeliveryAddressDataFactory
{
    protected function createInstance(): DeliveryAddressData
    {
        return new DeliveryAddressData();
    }

    public function create(): DeliveryAddressData
    {
        return $this->createInstance();
    }

    public function createFromDeliveryAddress(DeliveryAddress $deliveryAddress): DeliveryAddressData
    {
        $deliveryAddressData = $this->createInstance();
        $this->fillFromDeliveryAddress($deliveryAddressData, $deliveryAddress);

        return $deliveryAddressData;
    }

    public function createForCustomer(Customer $customer): DeliveryAddressData
    {
        $deliveryAddressData = $this->createInstance();
        $deliveryAddressData->customer = $customer;
        $deliveryAddressData->country = $customer->getBillingAddress()->getCountry();

        return $deliveryAddressData;
    }

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
        $deliveryAddressData->uuid = $deliveryAddress->getUuid();
    }
}
