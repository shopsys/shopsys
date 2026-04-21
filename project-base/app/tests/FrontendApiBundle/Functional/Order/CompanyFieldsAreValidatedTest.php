<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Functional\Order;

use App\DataFixtures\Demo\CartDataFixture;
use Shopsys\FrameworkBundle\Component\Translation\Translator;
use Shopsys\FrameworkBundle\Model\PhonePrefix\PhoneData;
use Tests\FrontendApiBundle\Test\GraphQlTestCase;

class CompanyFieldsAreValidatedTest extends GraphQlTestCase
{
    use OrderTestTrait;

    public function testValidationErrorWhenCompanyBehalfIsTrueAndFieldsAreMissing(): void
    {
        $firstDomainLocale = $this->getLocaleForFirstDomain();
        $expectedValidations = [
            'input.companyName' => [
                0 => [
                    'message' => t('Please enter company name', [], Translator::CUSTOMER_VALIDATOR_TRANSLATION_DOMAIN, $firstDomainLocale),
                    'code' => 'c1051bb4-d103-4f74-8988-acbcafc7fdc3',
                ],
            ],
            'input.companyNumber' => [
                0 => [
                    'message' => t('Please enter identification number', [], Translator::CUSTOMER_VALIDATOR_TRANSLATION_DOMAIN, $firstDomainLocale),
                    'code' => 'c1051bb4-d103-4f74-8988-acbcafc7fdc3',
                ],
            ],
        ];

        $this->addPplTransportToCart(CartDataFixture::CART_UUID);
        $this->addCardPaymentToDemoCart();

        $response = $this->getResponseContentForGql(
            __DIR__ . '/graphql/CreateMinimalOrderMutation.graphql',
            [
                'cartUuid' => CartDataFixture::CART_UUID,
                'firstName' => 'firstName',
                'lastName' => 'lastName',
                'email' => 'user@example.com',
                'telephone' => new PhoneData('CU', '+53', '123456789'),
                'onCompanyBehalf' => true,
                'street' => '123 Fake Street',
                'city' => 'Springfield',
                'postcode' => '12345',
                'country' => 'CZ',
                'note' => 'Thank You',
                'isDeliveryAddressDifferentFromBilling' => true,
                'deliveryFirstName' => 'deliveryFirstName',
                'deliveryLastName' => 'deliveryLastName',
                'deliveryStreet' => 'deliveryStreet',
                'deliveryCity' => 'deliveryCity',
                'deliveryCountry' => 'SK',
                'deliveryPostcode' => '13453',
            ],
        );

        $this->assertResponseContainsArrayOfExtensionValidationErrors($response);

        $this->assertEquals($expectedValidations, $this->getErrorsExtensionValidationFromResponse($response));
    }
}
