<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Functional\Complaint;

use App\DataFixtures\Demo\OrderDataFixture;
use App\Model\Product\Product;
use Tests\FrontendApiBundle\Test\GraphQlWithLoginTestCase;

class CreateComplaintTest extends GraphQlWithLoginTestCase
{
    public function testCreateComplaint(): void
    {
        /** @var \App\Model\Order\Order $order */
        $order = $this->getReference(OrderDataFixture::ORDER_PREFIX . 1);
        $orderItemProduct1 = $order->getProductItems()[0];
        $product1 = $orderItemProduct1->getProduct();
        $complaintItemQuantity1 = 1;
        $complaintItemFilesCount1 = 2;

        $orderItemProduct2 = $order->getProductItems()[1];
        $product2 = $orderItemProduct2->getProduct();
        $complaintItemQuantity2 = 2;
        $complaintItemFilesCount2 = 1;

        $response = $this->getResponseContentForGql(
            __DIR__ . '/graphql/CreateComplaintMutation.graphql',
            [
                'input' => [
                    'orderUuid' => $order->getUuid(),
                    'items' => [
                        [
                            'quantity' => $complaintItemQuantity1,
                            'description' => 'Broken!!!',
                            'orderItemUuid' => $orderItemProduct1->getUuid(),
                            'files' => [null, null],
                        ],
                        [
                            'quantity' => $complaintItemQuantity2,
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

        $this->assertComplaintItem(
            $responseData['items'][0],
            $complaintItemQuantity1,
            $complaintItemFilesCount1,
            $product1,
        );

        $this->assertComplaintItem(
            $responseData['items'][1],
            $complaintItemQuantity2,
            $complaintItemFilesCount2,
            $product2,
        );
    }

    /**
     * @param array $expectedComplaintItem
     * @param int $quantity
     * @param int $filesCount
     * @param \App\Model\Product\Product|null $product
     */
    private function assertComplaintItem(
        array $expectedComplaintItem,
        int $quantity,
        int $filesCount,
        ?Product $product = null,
    ): void {
        $this->assertArrayHasKey('quantity', $expectedComplaintItem);
        $this->assertSame($quantity, $expectedComplaintItem['quantity']);
        $this->assertArrayHasKey('files', $expectedComplaintItem);
        $this->assertCount($filesCount, $expectedComplaintItem['files']);
        $this->assertArrayHasKey('orderItem', $expectedComplaintItem);
        $this->assertArrayHasKey('name', $expectedComplaintItem['orderItem']);
        $this->assertArrayHasKey('productName', $expectedComplaintItem);
        $this->assertSame($product?->getName(), $expectedComplaintItem['productName']);
        $this->assertArrayHasKey('catnum', $expectedComplaintItem);
        $this->assertSame($product->getCatnum(), $expectedComplaintItem['catnum']);
    }
}
