<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Functional\Order;

use App\DataFixtures\Demo\ProductDataFixture;
use Shopsys\FrameworkBundle\Component\Translation\Translator;
use Tests\FrontendApiBundle\Test\GraphQlTestCase;

class MinimalOrderTest extends GraphQlTestCase
{
    use OrderTestTrait;

    public function testCreateMinimalOrderMutation(): void
    {
        $firstDomainLocale = $this->getLocaleForFirstDomain();
        $expectedOrderItems = $this->getExpectedOrderItems();
        $expected = [
            'data' => [
                'CreateOrder' => [
                    'order' => [
                        'transport' => [
                            'name' => t('Czech post', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale),
                        ],
                        'payment' => [
                            'name' => t('Cash on delivery', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale),
                        ],
                        'status' => t('New [adjective]', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale),
                        'totalPrice' => self::getSerializedOrderTotalPriceByExpectedOrderItems(
                            $expectedOrderItems,
                        ),
                        'items' => $expectedOrderItems,
                        'firstName' => 'firstName',
                        'lastName' => 'lastName',
                        'email' => 'user@example.com',
                        'telephone' => '+53 123456789',
                        'companyName' => null,
                        'companyNumber' => null,
                        'companyTaxNumber' => null,
                        'street' => '123 Fake Street',
                        'city' => 'Springfield',
                        'postcode' => '12345',
                        'country' => [
                            'code' => 'CZ',
                        ],
                        'differentDeliveryAddress' => false,
                        'deliveryFirstName' => 'firstName',
                        'deliveryLastName' => 'lastName',
                        'deliveryCompanyName' => null,
                        'deliveryTelephone' => '+53 123456789',
                        'deliveryStreet' => '123 Fake Street',
                        'deliveryCity' => 'Springfield',
                        'deliveryPostcode' => '12345',
                        'deliveryCountry' => [
                            'code' => 'CZ',
                        ],
                        'note' => null,
                    ],
                ],
            ],
        ];

        /** @var \Shopsys\FrameworkBundle\Model\Product\Product $product */
        $product = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . '1');

        $response = $this->getResponseContentForGql(__DIR__ . '/../_graphql/mutation/AddToCartMutation.graphql', [
            'productUuid' => $product->getUuid(),
            'quantity' => 1,
        ]);

        $cartUuid = $response['data']['AddToCart']['cart']['uuid'];
        $this->addCzechPostTransportToCart($cartUuid);
        $this->addCashOnDeliveryPaymentToCart($cartUuid);

        $this->assertQueryWithExpectedArray($this->getMutation($cartUuid), $expected);
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
                            onCompanyBehalf: false
                            street: "123 Fake Street"
                            city: "Springfield"
                            postcode: "12345"
                            country: "CZ"
                            differentDeliveryAddress: false
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
