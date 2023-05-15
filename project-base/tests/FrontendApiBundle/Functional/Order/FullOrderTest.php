<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Functional\Order;

use App\DataFixtures\Demo\PaymentDataFixture;
use App\DataFixtures\Demo\ProductDataFixture;
use App\DataFixtures\Demo\TransportDataFixture;
use App\DataFixtures\Demo\VatDataFixture;
use Shopsys\FrameworkBundle\Component\Translation\Translator;

class FullOrderTest extends AbstractOrderTestCase
{
    public function testCreateFullOrder(): void
    {
        $firstDomainLocale = $this->getLocaleForFirstDomain();
        $expectedOrderItems = $this->getExpectedOrderItems();
        $expected = [
            'data' => [
                'CreateOrder' => [
                    'transport' => [
                        'name' => t('Czech post', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale),
                    ],
                    'payment' => [
                        'name' => t('Cash on delivery', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale),
                    ],
                    'status' => t('New [adjective]', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale),
                    'totalPrice' => AbstractOrderTestCase::getSerializedOrderTotalPriceByExpectedOrderItems(
                        $expectedOrderItems,
                    ),
                    'items' => $expectedOrderItems,
                    'firstName' => 'firstName',
                    'lastName' => 'lastName',
                    'email' => 'user@example.com',
                    'telephone' => '+53 123456789',
                    'companyName' => 'Airlocks s.r.o.',
                    'companyNumber' => '1234',
                    'companyTaxNumber' => 'EU4321',
                    'street' => '123 Fake Street',
                    'city' => 'Springfield',
                    'postcode' => '12345',
                    'country' => 'CZ',
                    'differentDeliveryAddress' => true,
                    'deliveryFirstName' => 'deliveryFirstName',
                    'deliveryLastName' => 'deliveryLastName',
                    'deliveryCompanyName' => null,
                    'deliveryTelephone' => null,
                    'deliveryStreet' => 'deliveryStreet',
                    'deliveryCity' => 'deliveryCity',
                    'deliveryPostcode' => '13453',
                    'deliveryCountry' => 'SK',
                    'note' => 'Thank You',
                ],
            ],
        ];

        $this->assertQueryWithExpectedArray($this->getMutation(), $expected);
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

        return 'mutation {
                    CreateOrder(
                        input: {
                            firstName: "firstName"
                            lastName: "lastName"
                            email: "user@example.com"
                            telephone: "+53 123456789"
                            onCompanyBehalf: true
                            companyName: "Airlocks s.r.o."
                            companyNumber: "1234"
                            companyTaxNumber: "EU4321"
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
