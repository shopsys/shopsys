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

        $this->assertQueryWithExpectedArray($this->getMutation($cartUuid, $store->getUuid()), $expected);
    }

    /**
     * @param string $cartUuid
     * @param string $storeUuid
     * @return string
     */
    private function getMutation(string $cartUuid, string $storeUuid): string
    {
        $domainId = $this->domain->getId();
        /** @var \Shopsys\FrameworkBundle\Model\Pricing\Vat\Vat $vatHigh */
        $vatHigh = $this->getReferenceForDomain(VatDataFixture::VAT_HIGH, $domainId);
        /** @var \Shopsys\FrameworkBundle\Model\Pricing\Vat\Vat $vatZero */
        $vatZero = $this->getReferenceForDomain(VatDataFixture::VAT_ZERO, $domainId);

        /** @var \Shopsys\FrameworkBundle\Model\Payment\Payment $paymentCard */
        $paymentCard = $this->getReference(PaymentDataFixture::PAYMENT_CARD);
        $paymentPrice = $this->getMutationPriceConvertedToDomainDefaultCurrency('100', $vatZero);

        /** @var \Shopsys\FrameworkBundle\Model\Transport\Transport $transportPersonalPickup */
        $transportPersonalPickup = $this->getReference(TransportDataFixture::TRANSPORT_PERSONAL);
        $transportPrice = $this->getMutationPriceConvertedToDomainDefaultCurrency('0', $vatHigh);

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
                            transport: {
                                uuid: "' . $transportPersonalPickup->getUuid() . '"
                                price: ' . $transportPrice . '
                                pickupPlaceIdentifier: "' . $storeUuid . '"
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
}
