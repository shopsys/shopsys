<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Customer;

use InvalidArgumentException;
use Overblog\GraphQLBundle\Definition\Argument;
use Shopsys\FrameworkBundle\Model\Country\CountryFacade;
use Shopsys\FrameworkBundle\Model\Customer\Customer;
use Shopsys\FrameworkBundle\Model\Customer\DeliveryAddressData;
use Shopsys\FrameworkBundle\Model\Customer\DeliveryAddressDataFactoryInterface;

class DeliveryAddressDataApiFactory
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Country\CountryFacade $countryFacade
     * @param \Shopsys\FrameworkBundle\Model\Customer\DeliveryAddressDataFactoryInterface $deliveryAddressDataFactory
     */
    public function __construct(
        protected readonly CountryFacade $countryFacade,
        protected readonly DeliveryAddressDataFactoryInterface $deliveryAddressDataFactory,
    ) {
    }

    /**
     * @param \Overblog\GraphQLBundle\Definition\Argument $argument
     * @param \Shopsys\FrameworkBundle\Model\Customer\Customer $customer
     * @return \Shopsys\FrameworkBundle\Model\Customer\DeliveryAddressData
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

        $deliveryAddressData = $this->deliveryAddressDataFactory->create();
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
