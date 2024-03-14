<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Functional\Customer;

use App\DataFixtures\Demo\CountryDataFixture;
use App\DataFixtures\Demo\CustomerUserDataFixture;
use App\Model\Customer\DeliveryAddress;
use App\Model\Customer\DeliveryAddressDataFactory;
use App\Model\Customer\DeliveryAddressFacade;
use App\Model\Customer\User\CustomerUser;
use Tests\FrontendApiBundle\Test\GraphQlWithLoginTestCase;

class SetDefaultDeliveryAddressTest extends GraphQlWithLoginTestCase
{
    /**
     * @inject
     */
    private DeliveryAddressDataFactory $deliveryAddressDataFactory;

    /**
     * @inject
     */
    private DeliveryAddressFacade $deliveryAddressFacade;

    public function testSetDefaultDeliveryAddress(): void
    {
        /** @var \App\Model\Customer\User\CustomerUser $customerUser */
        $customerUser = $this->getReference(CustomerUserDataFixture::USER_WITH_RESET_PASSWORD_HASH);
        $oldDeliveryAddressUuid = $customerUser->getDefaultDeliveryAddress()->getUuid();
        $newDeliveryAddress = $this->createNewDeliveryAddress($customerUser);

        $response = $this->getResponseContentForGql(
            __DIR__ . '/../_graphql/mutation/SetDefaultDeliveryAddressMutation.graphql',
            [
                'deliveryAddressUuid' => $newDeliveryAddress->getUuid(),
            ],
        );

        $data = $this->getResponseDataForGraphQlType($response, 'SetDefaultDeliveryAddress');

        $this->assertNotEquals($oldDeliveryAddressUuid, $data['defaultDeliveryAddress']['uuid']);
        $this->assertEquals($newDeliveryAddress->getUuid(), $data['defaultDeliveryAddress']['uuid']);
    }

    /**
     * @param \App\Model\Customer\User\CustomerUser $customerUser
     * @return \App\Model\Customer\DeliveryAddress
     */
    private function createNewDeliveryAddress(CustomerUser $customerUser): DeliveryAddress
    {
        /** @var \Shopsys\FrameworkBundle\Model\Country\Country $country */
        $country = $this->getReference(CountryDataFixture::COUNTRY_CZECH_REPUBLIC);

        $deliveryAddressData = $this->deliveryAddressDataFactory->create();
        $deliveryAddressData->addressFilled = true;
        $deliveryAddressData->firstName = 'Jmeno';
        $deliveryAddressData->lastName = 'Prijmeni';
        $deliveryAddressData->street = 'Nova ulice';
        $deliveryAddressData->city = 'Nove mesto';
        $deliveryAddressData->postcode = 'Zip';
        $deliveryAddressData->country = $country;
        $deliveryAddressData->customer = $customerUser->getCustomer();

        return $this->deliveryAddressFacade->createIfAddressFilled($deliveryAddressData);
    }
}
