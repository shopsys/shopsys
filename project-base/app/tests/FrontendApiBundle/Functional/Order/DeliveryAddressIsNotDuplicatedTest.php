<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Functional\Order;

use App\DataFixtures\Demo\ProductDataFixture;
use App\DataFixtures\Demo\TransportDataFixture;
use App\Model\Product\Product;
use App\Model\Transport\Transport;
use Tests\FrontendApiBundle\Test\GraphQlWithLoginTestCase;

class DeliveryAddressIsNotDuplicatedTest extends GraphQlWithLoginTestCase
{
    public function testDeliveryAddressIsNotDuplicatedForLoggedInClient(): void
    {
        $this->initializeCart();

        $orderVariables = [
            'firstName' => 'firstName',
            'lastName' => 'lastName',
            'email' => 'user@example.com',
            'telephone' => '+53 123456789',
            'onCompanyBehalf' => false,
            'street' => '123 Fake Street',
            'city' => 'Springfield',
            'postcode' => '12345',
            'country' => 'CZ',
            'isDeliveryAddressDifferentFromBilling' => true,
            'deliveryFirstName' => 'deliveryFirstName',
            'deliveryLastName' => 'deliveryLastName',
            'deliveryCompanyName' => null,
            'deliveryTelephone' => null,
            'deliveryStreet' => 'deliveryStreet',
            'deliveryCity' => 'deliveryCity',
            'deliveryPostcode' => '46014',
            'deliveryCountry' => 'CZ',
        ];

        $this->getResponseContentForGql(__DIR__ . '/../_graphql/mutation/CreateOrderMutation.graphql', $orderVariables);

        $deliveryAddresses = $this->getCustomersDeliveryAddresses();
        $lastDeliveryAddressUuid = end($deliveryAddresses)['uuid'];

        $this->assertCount(2, $deliveryAddresses);

        $this->initializeCart();
        $this->getResponseContentForGql(
            __DIR__ . '/../_graphql/mutation/CreateOrderMutation.graphql',
            $orderVariables + [
                'deliveryAddressUuid' => $lastDeliveryAddressUuid,
            ],
        );

        $deliveryAddresses = $this->getCustomersDeliveryAddresses();

        $this->assertCount(2, $deliveryAddresses);
        $this->assertEquals($lastDeliveryAddressUuid, end($deliveryAddresses)['uuid']);
    }

    private function initializeCart(): void
    {
        $product = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . '1', Product::class);

        $transport = $this->getReference(TransportDataFixture::TRANSPORT_PPL, Transport::class);

        $this->getResponseContentForGql(__DIR__ . '/../_graphql/mutation/AddToCartMutation.graphql', [
            'productUuid' => $product->getUuid(),
            'quantity' => 1,
        ]);

        $this->getResponseContentForGql(__DIR__ . '/../_graphql/mutation/ChangeTransportInCartMutation.graphql', [
            'transportUuid' => $transport->getUuid(),
        ]);

        $this->getResponseContentForGql(__DIR__ . '/../_graphql/mutation/ChangePaymentInCartMutation.graphql', [
            'paymentUuid' => $transport->getPayments()[0]->getUuid(),
        ]);
    }

    /**
     * @return array<int, array{uuid: string}>
     */
    private function getCustomersDeliveryAddresses(): array
    {
        $response = $this->getResponseContentForGql(__DIR__ . '/../_graphql/query/CurrentCustomerUserQuery.graphql');

        return $this->getResponseDataForGraphQlType(
            $response,
            'currentCustomerUser',
        )['deliveryAddresses'];
    }
}
