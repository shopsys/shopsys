<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Functional\Customer;

use Shopsys\FrameworkBundle\Form\Constraints\Country;
use Tests\FrontendApiBundle\Test\GraphQlWithLoginTestCase;

class CreateDeliveryAddressTest extends GraphQlWithLoginTestCase
{
    public function testCreateDeliveryAddressForLoggedUser(): void
    {
        $deliveryAddressValues = [
            'firstName' => 'newFirstName',
            'lastName' => 'newLastName',
            'street' => 'newStreet',
            'city' => 'newCity',
            'postcode' => '46014',
            'country' => 'CZ',
            'companyName' => 'Shopsys',
            'telephone' => '777777777',
        ];

        $response = $this->getResponseContentForGql(
            __DIR__ . '/../_graphql/mutation/CreateDeliveryAddressMutation.graphql',
            $deliveryAddressValues,
        );

        $deliveryAddresses = $this->getResponseDataForGraphQlType($response, 'CreateDeliveryAddress');

        $this->assertCount(2, $deliveryAddresses);
    }

    public function testDeliveryAddressWithInvalidCountryForLoggedUser(): void
    {
        $deliveryAddressValues = [
            'firstName' => 'newFirstName',
            'lastName' => 'newLastName',
            'street' => 'newStreet',
            'city' => 'newCity',
            'postcode' => '46014',
            'country' => 'CZ1',
            'companyName' => 'Shopsys',
            'telephone' => '777777777',
        ];

        $response = $this->getResponseContentForGql(
            __DIR__ . '/../_graphql/mutation/CreateDeliveryAddressMutation.graphql',
            $deliveryAddressValues,
        );

        $errors = $this->getErrorsExtensionValidationFromResponse($response);
        $countryError = array_shift($errors)[0];

        $this->assertEquals(Country::INVALID_COUNTRY_ERROR, $countryError['code']);
    }
}
