<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Functional\Complaint;

use App\DataFixtures\Demo\ComplaintDataFixture;
use App\DataFixtures\Demo\OrderDataFixture;
use Shopsys\FrameworkBundle\Model\Complaint\Complaint;
use Shopsys\FrameworkBundle\Model\Complaint\ComplaintResolutionEnum;
use Shopsys\FrameworkBundle\Model\Complaint\Status\ComplaintStatusTypeEnum;
use Shopsys\FrameworkBundle\Model\Order\Item\OrderItem;
use Shopsys\FrameworkBundle\Model\Order\Order;
use Shopsys\FrameworkBundle\Model\PhonePrefix\PhoneData;
use Tests\FrontendApiBundle\Test\GraphQlWithLoginTestCase;

final class CreateComplaintQuantityTest extends GraphQlWithLoginTestCase
{
    public function testRemainingQuantityCanBeClaimedAcrossMultipleComplaints(): void
    {
        $order = $this->getReference(OrderDataFixture::ORDER_PREFIX . 3, Order::class);
        $orderItem = $order->getProductItems()[0];
        $expectedRemainingQuantity = 6;
        $this->assertSame($expectedRemainingQuantity, $orderItem->getQuantity());

        $firstResponse = $this->createComplaintForQuantities($order, $orderItem, [1]);
        $firstComplaint = $this->getResponseDataForGraphQlType($firstResponse, 'CreateComplaint');
        --$expectedRemainingQuantity;
        $this->assertSame($expectedRemainingQuantity, $firstComplaint['items'][0]['orderItem']['remainingComplaintQuantity']);

        $secondResponse = $this->createComplaintForQuantities($order, $orderItem, [$expectedRemainingQuantity]);
        $secondComplaint = $this->getResponseDataForGraphQlType($secondResponse, 'CreateComplaint');
        $this->assertSame(0, $secondComplaint['items'][0]['orderItem']['remainingComplaintQuantity']);

        $thirdResponse = $this->createComplaintForQuantities($order, $orderItem, [1]);

        $this->assertUserError($thirdResponse, 'invalid-quantity');
    }

    public function testDuplicateItemsInOneComplaintCannotExceedRemainingQuantity(): void
    {
        $order = $this->getReference(OrderDataFixture::ORDER_PREFIX . 3, Order::class);
        $orderItem = $order->getProductItems()[0];

        $response = $this->createComplaintForQuantities($order, $orderItem, [$orderItem->getQuantity(), 1]);

        $this->assertUserError($response, 'invalid-quantity');
    }

    public function testComplaintInResolvedStatusKeepsClaimedQuantity(): void
    {
        $complaint = $this->getReference(ComplaintDataFixture::COMPLAINT_RESOLVED_MONEY_RETURN, Complaint::class);
        $this->assertSame(ComplaintStatusTypeEnum::STATUS_TYPE_RESOLVED, $complaint->getStatus()->getStatusType());
        $orderItem = $complaint->getItems()[0]->getOrderItem();

        $response = $this->createComplaintForQuantities($complaint->getOrder(), $orderItem, [$orderItem->getQuantity()]);

        $this->assertUserError($response, 'invalid-quantity');
    }

    public function testComplaintInClaimableAgainStatusReleasesClaimedQuantity(): void
    {
        $complaint = $this->getReference(ComplaintDataFixture::COMPLAINT_REJECTED, Complaint::class);
        $this->assertSame(ComplaintStatusTypeEnum::STATUS_TYPE_RESOLVED_CLAIMABLE_AGAIN, $complaint->getStatus()->getStatusType());
        $orderItem = $complaint->getItems()[0]->getOrderItem();

        $response = $this->createComplaintForQuantities($complaint->getOrder(), $orderItem, [$orderItem->getQuantity()]);
        $newComplaint = $this->getResponseDataForGraphQlType($response, 'CreateComplaint');

        $this->assertSame(0, $newComplaint['items'][0]['orderItem']['remainingComplaintQuantity']);
    }

    /**
     * @param int[] $quantities
     * @return array<string, mixed>
     */
    private function createComplaintForQuantities(Order $order, OrderItem $orderItem, array $quantities): array
    {
        $items = [];
        $files = [];
        $fileMap = [];

        foreach ($quantities as $index => $quantity) {
            $items[] = [
                'orderItemUuid' => $orderItem->getUuid(),
                'quantity' => $quantity,
                'description' => 'Broken item',
                'files' => [null],
            ];
            $files[$index + 1] = __DIR__ . '/files/1.jpg';
            $fileMap[$index + 1] = ['variables.input.items.' . $index . '.files.0'];
        }

        return $this->getResponseContentForGql(
            __DIR__ . '/graphql/CreateComplaintMutation.graphql',
            [
                'input' => [
                    'orderUuid' => $order->getUuid(),
                    'email' => self::DEFAULT_USER_EMAIL,
                    'preferredResolution' => ComplaintResolutionEnum::FIX,
                    'items' => $items,
                    'deliveryAddress' => [
                        'firstName' => 'Jiří',
                        'lastName' => 'Ševčík',
                        'street' => 'První 1',
                        'city' => 'Ostrava',
                        'postcode' => '71200',
                        'telephone' => new PhoneData('CZ', '+420', '123456789'),
                        'country' => 'CZ',
                    ],
                ],
            ],
            $files,
            $fileMap,
        );
    }
}
