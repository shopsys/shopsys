<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Functional\Complaint;

use App\DataFixtures\Demo\OrderDataFixture;
use Tests\FrontendApiBundle\Test\GraphQlWithLoginTestCase;

class CreateComplaintTest extends GraphQlWithLoginTestCase
{
    public function testCreateComplaint(): void
    {
        /** @var \App\Model\Order\Order $order */
        $order = $this->getReference(OrderDataFixture::ORDER_PREFIX . 1);
        $orderItemProduct1 = $order->getProductItems()[0];
        $orderItemProduct2 = $order->getProductItems()[1];

        $response = $this->getResponseContentForGql(
            __DIR__ . '/graphql/CreateComplaintMutation.graphql',
            [
                'input' => [
                    'orderUuid' => $order->getUuid(),
                    'items' => [
                        [
                            'quantity' => 1,
                            'description' => 'Broken!!!',
                            'orderItemUuid' => $orderItemProduct1->getUuid(),
                            'files' => [null, null],
                        ],
                        [
                            'quantity' => 2,
                            'description' => 'Broken 2!!!',
                            'orderItemUuid' => $orderItemProduct2->getUuid(),
                            'files' => [null],
                        ],
                    ],
                    'deliveryAddress' => [
                        'firstName' => 'Jiří',
                        'lastName' => 'Ševčík',
                        'street' => 'První 1',
                        'city' => 'Ostrava',
                        'postcode' => '71200',
                        'telephone' => '+420123456789',
                        'country' => 'CZ',
                    ],
                ],
            ],
            [
                1 => __DIR__ . '/files/1.jpg',
                2 => __DIR__ . '/files/2.jpg',
                3 => __DIR__ . '/files/3.jpg',
            ],
            [
                1 => ['variables.input.items.0.files.0'],
                2 => ['variables.input.items.0.files.1'],
                3 => ['variables.input.items.1.files.0'],
            ],
        );

        $responseData = $this->getResponseDataForGraphQlType($response, 'CreateComplaint');

        $this->assertArrayHasKey('number', $responseData);

        $this->assertArrayHasKey('items', $responseData);
        $this->assertCount(2, $responseData['items']);

        $this->assertArrayHasKey('quantity', $responseData['items'][0]);
        $this->assertSame(1, $responseData['items'][0]['quantity']);
        $this->assertArrayHasKey('files', $responseData['items'][0]);
        $this->assertCount(2, $responseData['items'][0]['files']);
        $this->assertArrayHasKey('orderItem', $responseData['items'][0]);
        $this->assertArrayHasKey('name', $responseData['items'][0]['orderItem']);

        $this->assertArrayHasKey('quantity', $responseData['items'][1]);
        $this->assertSame(2, $responseData['items'][1]['quantity']);
        $this->assertArrayHasKey('files', $responseData['items'][1]);
        $this->assertCount(1, $responseData['items'][1]['files']);
        $this->assertArrayHasKey('orderItem', $responseData['items'][1]);
        $this->assertArrayHasKey('name', $responseData['items'][1]['orderItem']);
    }
}
