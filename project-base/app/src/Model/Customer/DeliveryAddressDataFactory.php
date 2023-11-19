<?php

declare(strict_types=1);

namespace App\Model\Customer;

use InvalidArgumentException;
use Overblog\GraphQLBundle\Definition\Argument;
use Shopsys\FrameworkBundle\Model\Country\CountryFacade;
use Shopsys\FrameworkBundle\Model\Customer\Customer;
use Shopsys\FrameworkBundle\Model\Customer\DeliveryAddress as BaseDeliveryAddress;
use Shopsys\FrameworkBundle\Model\Customer\DeliveryAddressData as BaseDeliveryAddressData;
use Shopsys\FrameworkBundle\Model\Customer\DeliveryAddressDataFactory as BaseDeliveryAddressDataFactory;

/**
 * @method \App\Model\Customer\DeliveryAddressData create()
 * @method \App\Model\Customer\DeliveryAddressData createFromDeliveryAddress(\App\Model\Customer\DeliveryAddress $deliveryAddress)
 */
class DeliveryAddressDataFactory extends BaseDeliveryAddressDataFactory
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Country\CountryFacade $countryFacade
     */
    public function __construct(private CountryFacade $countryFacade)
    {
    }

    /**
     * @return \App\Model\Customer\DeliveryAddressData
     */
    protected function createInstance(): DeliveryAddressData
    {
        return new DeliveryAddressData();
    }

    /**
     * @param \App\Model\Customer\DeliveryAddressData $deliveryAddressData
     * @param \App\Model\Customer\DeliveryAddress $deliveryAddress
     */
    protected function fillFromDeliveryAddress(
        BaseDeliveryAddressData $deliveryAddressData,
        BaseDeliveryAddress $deliveryAddress,
    ): void {
        parent::fillFromDeliveryAddress($deliveryAddressData, $deliveryAddress);

        $deliveryAddressData->uuid = $deliveryAddress->getUuid();
    }

    /**
     * @param \Overblog\GraphQLBundle\Definition\Argument $argument
     * @param \Shopsys\FrameworkBundle\Model\Customer\Customer $customer
     * @return \App\Model\Customer\DeliveryAddressData
     */
    public function createFromDeliveryInputArgumentAndCustomer(
        Argument $argument,
        Customer $customer,
    ): DeliveryAddressData {
        $input = $argument['input'];

        $isValidArray = isset(
            $input['firstName'],
            $input['lastName'],
            $input['street'],
            $input['city'],
            $input['postcode'],
            $input['country'],
        );

        if (!$isValidArray) {
            throw new InvalidArgumentException('DeliveryAddressInput is not valid.');
        }

        $country = $this->countryFacade->findByCode($input['country']);

        $deliveryAddressData = $this->create();
        $deliveryAddressData->uuid = $input['uuid'];
        $deliveryAddressData->firstName = $input['firstName'];
        $deliveryAddressData->lastName = $input['lastName'];
        $deliveryAddressData->companyName = $input['companyName'];
        $deliveryAddressData->street = $input['street'];
        $deliveryAddressData->city = $input['city'];
        $deliveryAddressData->postcode = $input['postcode'];
        $deliveryAddressData->telephone = $input['telephone'];
        $deliveryAddressData->country = $country;
        $deliveryAddressData->customer = $customer;

        return $deliveryAddressData;
    }
}
