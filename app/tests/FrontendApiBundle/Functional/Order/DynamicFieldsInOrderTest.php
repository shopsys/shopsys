<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Functional\Order;

use App\DataFixtures\Demo\PaymentDataFixture;
use App\DataFixtures\Demo\ProductDataFixture;
use App\DataFixtures\Demo\VatDataFixture;

class DynamicFieldsInOrderTest extends AbstractOrderTestCase
{
    public function testHasDynamicFields(): void
    {
        $graphQlType = 'CreateOrder';
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
        $this->addCzechPostTransportToCart($cartUuid);
        $response = $this->getResponseContentForQuery($this->getMutation($cartUuid));

        $this->assertResponseContainsArrayOfDataForGraphQlType($response, $graphQlType);
        $responseData = $this->getResponseDataForGraphQlType($response, $graphQlType);

        $this->assertArrayHasKey('uuid', $responseData);
        $this->assertIsString($responseData['uuid']);

        $this->assertArrayHasKey('number', $responseData);
        $this->assertIsString($responseData['number']);

        $this->assertArrayHasKey('urlHash', $responseData);
        $this->assertIsString($responseData['urlHash']);

        $this->assertArrayHasKey('creationDate', $responseData);
        $this->assertIsString($responseData['creationDate']);
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

        /** @var \Shopsys\FrameworkBundle\Model\Payment\Payment $paymentCashOnDelivery */
        $paymentCashOnDelivery = $this->getReference(PaymentDataFixture::PAYMENT_CASH_ON_DELIVERY);
        $paymentPrice = $this->getMutationPriceConvertedToDomainDefaultCurrency('50', $vatZero);

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
                            payment: {
                                uuid: "' . $paymentCashOnDelivery->getUuid() . '"
                                price: ' . $paymentPrice . '
                            }
                            differentDeliveryAddress: true
                            deliveryFirstName: "deliveryFirstName"
                            deliveryLastName: "deliveryLastName"
                            deliveryStreet: "deliveryStreet"
                            deliveryCity: "deliveryCity"
                            deliveryCountry: "SK"
                            deliveryPostcode: "13453"
                        }
                    ) {
                        uuid
                        number
                        urlHash
                        creationDate
                    }
                }';
    }
}
