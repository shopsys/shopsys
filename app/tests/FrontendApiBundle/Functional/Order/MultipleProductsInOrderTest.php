<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Functional\Order;

use App\DataFixtures\Demo\ProductDataFixture;
use App\DataFixtures\Demo\VatDataFixture;

class MultipleProductsInOrderTest extends AbstractOrderTestCase
{
    public function testCreateFullOrder(): void
    {
        $firstDomainLocale = $this->getLocaleForFirstDomain();
        $expectedOrderItems = $this->getExpectedOrderItems();
        $expected = [
            'data' => [
                'CreateOrder' => [
                    'transport' => [
                        'name' => t('Czech post', [], 'dataFixtures', $firstDomainLocale),
                    ],
                    'payment' => [
                        'name' => t('Cash on delivery', [], 'dataFixtures', $firstDomainLocale),
                    ],
                    'status' => t('New [adjective]', [], 'dataFixtures', $firstDomainLocale),
                    'totalPrice' => AbstractOrderTestCase::getSerializedOrderTotalPriceByExpectedOrderItems(
                        $expectedOrderItems
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
                    'country' => [
                        'code' => 'CZ',
                    ],
                    'differentDeliveryAddress' => true,
                    'deliveryFirstName' => 'deliveryFirstName',
                    'deliveryLastName' => 'deliveryLastName',
                    'deliveryCompanyName' => null,
                    'deliveryTelephone' => null,
                    'deliveryStreet' => 'deliveryStreet',
                    'deliveryCity' => 'deliveryCity',
                    'deliveryPostcode' => '13453',
                    'deliveryCountry' => [
                        'code' => 'SK',
                    ],
                    'note' => 'Thank You',
                ],
            ],
        ];
        $cartUuid = $this->addProductsToCart();
        $this->addCzechPostTransportToCart($cartUuid);
        $this->addCashOnDeliveryPaymentToCart($cartUuid);

        $this->assertQueryWithExpectedArray($this->getMutation($cartUuid), $expected);
    }

    /**
     * @return array
     */
    protected function getExpectedOrderItems(): array
    {
        $firstDomainLocale = $this->getLocaleForFirstDomain();
        $domainId = $this->domain->getId();
        /** @var \Shopsys\FrameworkBundle\Model\Pricing\Vat\Vat $vatHigh */
        $vatHigh = $this->getReferenceForDomain(VatDataFixture::VAT_HIGH, $domainId);
        /** @var \Shopsys\FrameworkBundle\Model\Pricing\Vat\Vat $vatZero */
        $vatZero = $this->getReferenceForDomain(VatDataFixture::VAT_ZERO, $domainId);

        return [
            [
                'name' => t('100 Czech crowns ticket', [], 'dataFixtures', $firstDomainLocale),
                'unitPrice' => $this->getSerializedPriceConvertedToDomainDefaultCurrency('100', $vatHigh),
                'totalPrice' => $this->getSerializedPriceConvertedToDomainDefaultCurrency('100', $vatHigh, 2),
                'quantity' => 2,
                'vatRate' => '21.0000',
                'unit' => t('pcs', [], 'dataFixtures', $firstDomainLocale),
            ], [
                'name' => t('Televize 22" Sencor SLE 22F46DM4 HELLO KITTY plazmová', [], 'dataFixtures', $firstDomainLocale),
                'unitPrice' => $this->getSerializedPriceConvertedToDomainDefaultCurrency('2891.70', $vatHigh),
                'totalPrice' => $this->getSerializedPriceConvertedToDomainDefaultCurrency('2891.70', $vatHigh),
                'quantity' => 1,
                'vatRate' => '21.0000',
                'unit' => t('pcs', [], 'dataFixtures', $firstDomainLocale),
            ], [
                'name' => t('Cash on delivery', [], 'dataFixtures', $firstDomainLocale),
                'unitPrice' => $this->getSerializedPriceConvertedToDomainDefaultCurrency('50', $vatZero),
                'totalPrice' => $this->getSerializedPriceConvertedToDomainDefaultCurrency('50', $vatZero),
                'quantity' => 1,
                'vatRate' => '0.0000',
                'unit' => null,
            ], [
                'name' => t('Czech post', [], 'dataFixtures', $firstDomainLocale),
                'unitPrice' => $this->getSerializedPriceConvertedToDomainDefaultCurrency('100', $vatHigh),
                'totalPrice' => $this->getSerializedPriceConvertedToDomainDefaultCurrency('100', $vatHigh),
                'quantity' => 1,
                'vatRate' => '21.0000',
                'unit' => null,
            ],
        ];
    }

    /**
     * @param string $cartUuid
     * @return string
     */
    private function getMutation(string $cartUuid): string
    {
        return 'mutation {
                    CreateOrder(
                        input: {
                            cartUuid: "' . $cartUuid . '"
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
                            differentDeliveryAddress: true
                            deliveryFirstName: "deliveryFirstName"
                            deliveryLastName: "deliveryLastName"
                            deliveryStreet: "deliveryStreet"
                            deliveryCity: "deliveryCity"
                            deliveryCountry: "SK"
                            deliveryPostcode: "13453"
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
                }';
    }

    /**
     * @return string
     */
    private function addProductsToCart(): string
    {
        /** @var \Shopsys\FrameworkBundle\Model\Product\Product $product1 */
        $product1 = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . '1');
        $mutation = 'mutation {
            AddToCart(input: {
                productUuid: "' . $product1->getUuid() . '",
                quantity: 1
            }) {
                cart {
                    uuid
                }
            }
        }';
        $cartUuid = $this->getResponseContentForQuery($mutation)['data']['AddToCart']['cart']['uuid'];

        /** @var \Shopsys\FrameworkBundle\Model\Product\Product $product72 */
        $product72 = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . '72');
        $mutation = 'mutation {
            AddToCart(input: {
                productUuid: "' . $product72->getUuid() . '",
                quantity: 2
                cartUuid: "' . $cartUuid . '"
            }) {
                cart {
                    uuid
                }
            }
        }';
        $this->getResponseContentForQuery($mutation);

        return $cartUuid;
    }
}
