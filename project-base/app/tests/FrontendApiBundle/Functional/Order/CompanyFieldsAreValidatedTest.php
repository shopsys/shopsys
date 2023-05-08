<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Functional\Order;

use App\DataFixtures\Demo\PaymentDataFixture;
use App\DataFixtures\Demo\ProductDataFixture;
use App\DataFixtures\Demo\TransportDataFixture;
use App\DataFixtures\Demo\VatDataFixture;
use Shopsys\FrameworkBundle\Component\Translation\Translator;

class CompanyFieldsAreValidatedTest extends AbstractOrderTestCase
{
    public function testValidationErrorWhenCompanyBehalfIsTrueAndFieldsAreMissing(): void
    {
        $firstDomainLocale = $this->getLocaleForFirstDomain();
        $expectedValidations = [
            'input.companyName' => [
                0 => [
                    'message' => t('Please enter company name', [], Translator::VALIDATOR_TRANSLATION_DOMAIN, $firstDomainLocale),
                    'code' => 'c1051bb4-d103-4f74-8988-acbcafc7fdc3',
                ],
            ],
            'input.companyNumber' => [
                0 => [
                    'message' => t('Please enter identification number', [], Translator::VALIDATOR_TRANSLATION_DOMAIN, $firstDomainLocale),
                    'code' => 'c1051bb4-d103-4f74-8988-acbcafc7fdc3',
                ],
            ],
        ];

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
        /** @var \Shopsys\FrameworkBundle\Model\Pricing\Vat\Vat $vatHigh */
        $vatHigh = $this->getReferenceForDomain(VatDataFixture::VAT_HIGH, $domainId);
        /** @var \Shopsys\FrameworkBundle\Model\Pricing\Vat\Vat $vatZero */
        $vatZero = $this->getReferenceForDomain(VatDataFixture::VAT_ZERO, $domainId);

        /** @var \Shopsys\FrameworkBundle\Model\Product\Product $product1 */
        $product1 = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . '1');
        $product1UnitPrice = $this->getMutationPriceConvertedToDomainDefaultCurrency('2891.70', $vatHigh);

        /** @var \Shopsys\FrameworkBundle\Model\Payment\Payment $paymentCashOnDelivery */
        $paymentCashOnDelivery = $this->getReference(PaymentDataFixture::PAYMENT_CASH_ON_DELIVERY);
        $paymentPrice = $this->getMutationPriceConvertedToDomainDefaultCurrency('50', $vatZero);

        /** @var \Shopsys\FrameworkBundle\Model\Transport\Transport $transportCzechPost */
        $transportCzechPost = $this->getReference(TransportDataFixture::TRANSPORT_CZECH_POST);
        $transportPrice = $this->getMutationPriceConvertedToDomainDefaultCurrency('100', $vatHigh);

        return '
            mutation {
                CreateOrder(
                    input: {
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
                        payment: {
                            uuid: "' . $paymentCashOnDelivery->getUuid() . '"
                            price: ' . $paymentPrice . '
                        }
                        transport: {
                            uuid: "' . $transportCzechPost->getUuid() . '"
                            price: ' . $transportPrice . '
                        }
                        differentDeliveryAddress: true
                        deliveryFirstName: "deliveryFirstName"
                        deliveryLastName: "deliveryLastName"
                        deliveryStreet: "deliveryStreet"
                        deliveryCity: "deliveryCity"
                        deliveryCountry: "SK"
                        deliveryPostcode: "13453"
                        products: [
                            {
                                uuid: "' . $product1->getUuid() . '",
                                unitPrice: ' . $product1UnitPrice . ',
                                quantity: 10
                            },
                        ]
                    }
                ) {
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
                    country
                    differentDeliveryAddress
                    deliveryFirstName
                    deliveryLastName
                    deliveryCompanyName
                    deliveryTelephone
                    deliveryStreet
                    deliveryCity
                    deliveryPostcode
                    deliveryCountry
                    note
                }
            }';
    }
}
