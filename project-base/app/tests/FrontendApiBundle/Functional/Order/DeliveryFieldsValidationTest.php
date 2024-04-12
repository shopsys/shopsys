<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Functional\Order;

use App\DataFixtures\Demo\CartDataFixture;
use Tests\FrontendApiBundle\Test\GraphQlTestCase;

class DeliveryFieldsValidationTest extends GraphQlTestCase
{
    use OrderTestTrait;

    private const array DEFAULT_INPUT_VALUES = [
        'cartUuid' => CartDataFixture::CART_UUID,
        'firstName' => 'firstName',
        'lastName' => 'lastName',
        'email' => 'user@example.com',
        'telephone' => '+53 123456789',
        'street' => '123 Fake Street',
        'city' => 'Springfield',
        'postcode' => '12345',
        'country' => 'CZ',
    ];

    public function testValidationErrorWhenDifferentDeliveryAddressIsTrueAndFieldsAreMissing(): void
    {
        $firstDomainLocale = $this->getLocaleForFirstDomain();
        $expectedValidations = [
            'input.deliveryFirstName' => [
                0 => [
                    'message' => t('Please enter first name of contact person', [], 'validators', $firstDomainLocale),
                    'code' => 'c1051bb4-d103-4f74-8988-acbcafc7fdc3',
                ],
            ],
            'input.deliveryLastName' => [
                0 => [
                    'message' => t('Please enter last name of contact person', [], 'validators', $firstDomainLocale),
                    'code' => 'c1051bb4-d103-4f74-8988-acbcafc7fdc3',
                ],
            ],
            'input.deliveryStreet' => [
                0 => [
                    'message' => t('Please enter street', [], 'validators', $firstDomainLocale),
                    'code' => 'c1051bb4-d103-4f74-8988-acbcafc7fdc3',
                ],
            ],
            'input.deliveryCity' => [
                0 => [
                    'message' => t('Please enter city', [], 'validators', $firstDomainLocale),
                    'code' => 'c1051bb4-d103-4f74-8988-acbcafc7fdc3',
                ],
            ],
            'input.deliveryPostcode' => [
                0 => [
                    'message' => t('Please enter zip code', [], 'validators', $firstDomainLocale),
                    'code' => 'c1051bb4-d103-4f74-8988-acbcafc7fdc3',
                ],
            ],
            'input.deliveryCountry' => [
                0 => [
                    'message' => t('Please choose country', [], 'validators', $firstDomainLocale),
                    'code' => 'c1051bb4-d103-4f74-8988-acbcafc7fdc3',
                ],
            ],
        ];

        $this->addPplTransportToCart(CartDataFixture::CART_UUID);
        $this->addCardPaymentToDemoCart();
        $response = $this->getResponseContentForGql(__DIR__ . '/graphql/CreateOrderWithDeliveryAddressValidationMutation.graphql', [
            ...self::DEFAULT_INPUT_VALUES,
            'differentDeliveryAddress' => true,
        ]);
        $this->assertResponseContainsArrayOfExtensionValidationErrors($response);

        $this->assertEquals($expectedValidations, $this->getErrorsExtensionValidationFromResponse($response));
    }

    public function testDeliveryFieldsAreNotValidatedWhenDifferentDeliveryAddressIsFalse(): void
    {
        $this->addPplTransportToCart(CartDataFixture::CART_UUID);
        $this->addCardPaymentToDemoCart();
        $response = $this->getResponseContentForGql(__DIR__ . '/graphql/CreateOrderWithDeliveryAddressValidationMutation.graphql', [
            ...self::DEFAULT_INPUT_VALUES,
            'differentDeliveryAddress' => false,
        ]);
        $this->assertResponseContainsArrayOfDataForGraphQlType($response, 'CreateOrder');
    }

    public function testLoginIsRequiredWhenDeliveryAddressUuidIsSet(): void
    {
        $this->addPplTransportToCart(CartDataFixture::CART_UUID);
        $this->addCardPaymentToDemoCart();
        $response = $this->getResponseContentForGql(__DIR__ . '/graphql/CreateOrderWithDeliveryAddressValidationMutation.graphql', [
            ...self::DEFAULT_INPUT_VALUES,
            'differentDeliveryAddress' => true,
            'deliveryAddressUuid' => '00000000-0000-0000-0000-000000000000',
        ]);

        $expectedValidations = [
            'input' => [
                0 => [
                    'message' => 'You must be logged in if you want to provide the delivery address UUID in the order input',
                    'code' => '9dcda0d3-7264-4c5f-9b35-f5b155f997f9',
                ],
            ],
        ];

        $this->assertResponseContainsArrayOfExtensionValidationErrors($response);
        $this->assertEquals($expectedValidations, $this->getErrorsExtensionValidationFromResponse($response));
    }
}
