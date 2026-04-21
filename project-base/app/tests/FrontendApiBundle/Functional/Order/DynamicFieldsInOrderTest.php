<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Functional\Order;

use App\DataFixtures\Demo\ProductDataFixture;
use App\Model\Product\Product;
use Shopsys\FrameworkBundle\Model\PhonePrefix\PhoneData;
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
        $response = $this->getResponseContentForGql(
            __DIR__ . '/graphql/CreateOrderWithDynamicFieldsMutation.graphql',
            [
                'cartUuid' => $cartUuid,
                'firstName' => 'firstName',
                'lastName' => 'lastName',
                'email' => 'user@example.com',
                'telephone' => new PhoneData('CU', '+53', '123456789'),
                'onCompanyBehalf' => true,
                'companyName' => 'Airlocks s.r.o.',
                'companyNumber' => '1234',
                'companyTaxNumber' => 'EU4321',
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
}
