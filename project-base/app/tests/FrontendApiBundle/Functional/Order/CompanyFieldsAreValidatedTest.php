<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Functional\Order;

use App\DataFixtures\Demo\CartDataFixture;
use App\DataFixtures\Demo\VatDataFixture;
use Shopsys\FrameworkBundle\Model\Pricing\Vat\Vat;
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
                    'message' => t('Please enter company name', [], 'validators', $firstDomainLocale),
                    'code' => 'c1051bb4-d103-4f74-8988-acbcafc7fdc3',
                ],
            ],
            'input.companyNumber' => [
                0 => [
                    'message' => t('Please enter identification number', [], 'validators', $firstDomainLocale),
                    'code' => 'c1051bb4-d103-4f74-8988-acbcafc7fdc3',
                ],
            ],
        ];

        $this->addPplTransportToCart(CartDataFixture::CART_UUID);
        $this->addCardPaymentToDemoCart();
        $response = $this->getResponseContentForQuery($this->getMutation());
        $this->assertResponseContainsArrayOfExtensionValidationErrors($response);

        $this->assertEquals($expectedValidations, $this->getErrorsExtensionValidationFromResponse($response));
    }

    /**
     * @return string
     */
    private function getMutation(): string
    {
        $domainId = $this->domain->getId();
        $vatHigh = $this->getReferenceForDomain(VatDataFixture::VAT_HIGH, $domainId, Vat::class);

        $product1UnitPrice = $this->getMutationPriceConvertedToDomainDefaultCurrency('2891.70', $vatHigh);

        return '
            mutation {
                CreateOrder(
                    input: {
                        cartUuid: "' . CartDataFixture::CART_UUID . '"
                        firstName: "firstName"
                        lastName: "lastName"
                        email: "user@example.com"
                        telephone: "+53 123456789"
                        onCompanyBehalf: true
                        street: "123 Fake Street"
                        city: "Springfield"
                        postcode: "12345"
                        country: "CZ"
                        note:"Thank You"
                        differentDeliveryAddress: true
                        deliveryFirstName: "deliveryFirstName"
                        deliveryLastName: "deliveryLastName"
                        deliveryStreet: "deliveryStreet"
                        deliveryCity: "deliveryCity"
                        deliveryCountry: "SK"
                        deliveryPostcode: "13453"
                    }
                ) {
                    order {
                        transport {
                            name
                        }
                        payment {
                            name
                        }
                        status
                        totalPrice {
                            priceWithVat
                            priceWithoutVat
                            vatAmount
                        }
                        items {
                            name
                            unitPrice {
                                priceWithVat
                                priceWithoutVat
                                vatAmount
                            }
                            totalPrice {
                                priceWithVat
                                priceWithoutVat
                                vatAmount
                            }
                            quantity
                            vatRate
                            unit
                        }
                        firstName
                        lastName
                        email
                        telephone
                        companyName
                        companyNumber
                        companyTaxNumber
                        street
                        city
                        postcode
                        country {
                            code
                        }
                        differentDeliveryAddress
                        deliveryFirstName
                        deliveryLastName
                        deliveryCompanyName
                        deliveryTelephone
                        deliveryStreet
                        deliveryCity
                        deliveryPostcode
                        deliveryCountry {
                            code
                        }
                        note
                    }
                }
            }';
    }
}
