<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Functional\Order;

use App\DataFixtures\Demo\ProductDataFixture;
use App\Model\Product\Product;
use Tests\FrontendApiBundle\Test\GraphQlTestCase;

class DynamicFieldsInOrderTest extends GraphQlTestCase
{
    use OrderTestTrait;

    public function testHasDynamicFields(): void
    {
        $graphQlType = 'CreateOrder';
        $product = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . '1', Product::class);

        $response = $this->getResponseContentForGql(__DIR__ . '/../_graphql/mutation/AddToCartMutation.graphql', [
            'productUuid' => $product->getUuid(),
            'quantity' => 1,
        ]);

        $cartUuid = $response['data']['AddToCart']['cart']['uuid'];
        $this->addCzechPostTransportToCart($cartUuid);
        $this->addCashOnDeliveryPaymentToCart($cartUuid);
        $response = $this->getResponseContentForQuery($this->getMutation($cartUuid));

        $this->assertResponseContainsArrayOfDataForGraphQlType($response, $graphQlType);
        $responseData = $this->getResponseDataForGraphQlType($response, $graphQlType);
        $this->assertArrayHasKey('order', $responseData);
        $orderData = $responseData['order'];

        $this->assertArrayHasKey('uuid', $orderData);
        $this->assertIsString($orderData['uuid']);

        $this->assertArrayHasKey('number', $orderData);
        $this->assertIsString($orderData['number']);

        $this->assertArrayHasKey('urlHash', $orderData);
        $this->assertIsString($orderData['urlHash']);

        $this->assertArrayHasKey('creationDate', $orderData);
        $this->assertIsString($orderData['creationDate']);
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
                        order {
                            uuid
                            number
                            urlHash
                            creationDate
                        }
                    }
                }';
    }
}
