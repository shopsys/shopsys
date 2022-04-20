<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Functional\Order;

use App\DataFixtures\Demo\PaymentDataFixture;
use App\DataFixtures\Demo\ProductDataFixture;
use App\DataFixtures\Demo\StoreDataFixture;
use App\DataFixtures\Demo\TransportDataFixture;
use App\DataFixtures\Demo\VatDataFixture;
use Tests\FrontendApiBundle\Test\GraphQlTestCase;

class OrderWithPersonalPickupStoreTest extends GraphQlTestCase
{
    public function testCreateOrderWithPersonalPickupStore()
    {
        /** @var \App\Model\Store\Store $store */
        $store = $this->getReference(StoreDataFixture::STORE_PREFIX . 1);

        $expected = [
            'data' => [
                'CreateOrder' => [
                    'deliveryFirstName' => 'firstName',
                    'deliveryLastName' => 'lastName',
                    'deliveryCompanyName' => 'Shopsys',
                    'deliveryTelephone' => '+53 123456789',
                    'deliveryStreet' => $store->getStreet(),
                    'deliveryCity' => $store->getCity(),
                    'deliveryPostcode' => $store->getPostcode(),
                    'deliveryCountry' => [
                        'code' => $store->getCountry()->getCode(),
                    ],
                ],
            ],
        ];

        /** @var \Shopsys\FrameworkBundle\Model\Product\Product $product */
        $product = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . '1');
        $mutation = 'mutation {
            AddToCart(input: {
                productUuid: "' . $product->getUuid() . '",
                quantity: 1
            }) {
                uuid
            }
        }';
        $cartUuid = $this->getResponseContentForQuery($mutation)['data']['AddToCart']['uuid'];

        $this->addPersonalPickupTransportToCart($cartUuid, $store->getUuid());

        $this->assertQueryWithExpectedArray($this->getMutation($cartUuid), $expected);
    }

    /**
     * @param string $cartUuid
     * @return string
     */
    private function getMutation(string $cartUuid): string
    {
        $domainId = $this->domain->getId();
        /** @var \Shopsys\FrameworkBundle\Model\Pricing\Vat\Vat $vatZero */
        $vatZero = $this->getReferenceForDomain(VatDataFixture::VAT_ZERO, $domainId);

        /** @var \Shopsys\FrameworkBundle\Model\Payment\Payment $paymentCard */
        $paymentCard = $this->getReference(PaymentDataFixture::PAYMENT_CARD);
        $paymentPrice = $this->getMutationPriceConvertedToDomainDefaultCurrency('100', $vatZero);

        return 'mutation {
                    CreateOrder(
                        input: {
                            cartUuid: "' . $cartUuid . '"
                            firstName: "firstName"
                            lastName: "lastName"
                            companyName: "Shopsys"
                            email: "user@example.com"
                            telephone: "+53 123456789"
                            onCompanyBehalf: false
                            street: "123 Fake Street"
                            city: "Springfield"
                            postcode: "12345"
                            country: "CZ"
                            payment: {
                                uuid: "' . $paymentCard->getUuid() . '"
                                price: ' . $paymentPrice . '
                            }
                            differentDeliveryAddress: false
                        }
                    ) {
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
                    }
                }';
    }

    /**
     * @param string $cartUuid
     * @param string $pickupPlaceIdentifier
     */
    private function addPersonalPickupTransportToCart(string $cartUuid, string $pickupPlaceIdentifier): void
    {
        /** @var \App\Model\Transport\Transport $transportPersonalPickup */
        $transportPersonalPickup = $this->getReference(TransportDataFixture::TRANSPORT_PERSONAL);
        $changeTransportInCartMutation = '
            mutation {
                ChangeTransportInCart(input:{
                    cartUuid: "' . $cartUuid . '"
                    transportUuid: "' . $transportPersonalPickup->getUuid() . '"
                    pickupPlaceIdentifier: "' . $pickupPlaceIdentifier . '"
                }) {
                    uuid
                }
            }
        ';
        $this->getResponseContentForQuery($changeTransportInCartMutation);
    }
}
