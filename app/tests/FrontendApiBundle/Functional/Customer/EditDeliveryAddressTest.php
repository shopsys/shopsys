<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Functional\Customer;

use App\DataFixtures\Demo\CustomerUserDataFixture;
use Shopsys\FrameworkBundle\Component\Translation\Translator;
use Shopsys\FrameworkBundle\Form\Constraints\Country;
use Tests\FrontendApiBundle\Test\GraphQlWithLoginTestCase;

class EditDeliveryAddressTest extends GraphQlWithLoginTestCase
{
    public function testEditDeliveryAddress(): void
    {
        /** @var \App\Model\Customer\User\CustomerUser $customer */
        $customer = $this->getReference(CustomerUserDataFixture::USER_WITH_RESET_PASSWORD_HASH);
        $deliveryAddress = $customer->getDefaultDeliveryAddress();

        $editedValues = [
            'uuid' => $deliveryAddress->getUuid(),
            'firstName' => 'editedFirstName',
            'lastName' => 'editedLastName',
            'street' => 'editedStreet',
            'city' => 'editedCity',
            'postcode' => '46014',
            'country' => 'CZ',
            'companyName' => 'Shopsys',
            'telephone' => '777777777',
        ];

        $response = $this->getResponseContentForGql(
            __DIR__ . '/../_graphql/mutation/EditDeliveryAddressMutation.graphql',
            $editedValues
        );

        $deliveryAddresses = $this->getResponseDataForGraphQlType($response, 'EditDeliveryAddress');
        $editedAddressKey = array_search(
            $deliveryAddress->getUuid(),
            array_column($deliveryAddresses, 'uuid'),
            true
        );

        $this->assertEquals($deliveryAddress->getUuid(), $deliveryAddresses[$editedAddressKey]['uuid']);

        $expectedValues = array_merge($editedValues, [
            'country' => [
                'code' => 'CZ',
                'name' => t('Czech republic', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->getFirstDomainLocale()),
            ],
        ]);

        $this->assertSame($expectedValues, $deliveryAddresses[$editedAddressKey]);
    }

    public function testEditDeliveryAddressWithInvalidCountry(): void
    {
        /** @var \App\Model\Customer\User\CustomerUser $customer */
        $customer = $this->getReference(CustomerUserDataFixture::USER_WITH_RESET_PASSWORD_HASH);
        $deliveryAddress = $customer->getDefaultDeliveryAddress();

        $editedValues = [
            'uuid' => $deliveryAddress->getUuid(),
            'firstName' => 'editedFirstName',
            'lastName' => 'editedLastName',
            'street' => 'editedStreet',
            'city' => 'editedCity',
            'postcode' => '46014',
            'country' => 'CZ1',
            'companyName' => 'Shopsys',
            'telephone' => '777777777',
        ];

        $response = $this->getResponseContentForGql(
            __DIR__ . '/../_graphql/mutation/EditDeliveryAddressMutation.graphql',
            $editedValues
        );

        $errors = $this->getErrorsExtensionValidationFromResponse($response);
        $countryError = array_shift($errors)[0];

        $this->assertEquals(Country::INVALID_COUNTRY_ERROR, $countryError['code']);
    }
}
