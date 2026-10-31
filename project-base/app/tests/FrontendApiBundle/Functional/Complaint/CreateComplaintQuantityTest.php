<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Functional\Complaint;

use App\DataFixtures\Demo\ComplaintStatusDataFixture;
use App\DataFixtures\Demo\OrderDataFixture;
use Doctrine\ORM\EntityManagerInterface;
use Shopsys\FrameworkBundle\Model\Complaint\Complaint;
use Shopsys\FrameworkBundle\Model\Complaint\ComplaintDataFactory;
use Shopsys\FrameworkBundle\Model\Complaint\ComplaintFacade;
use Shopsys\FrameworkBundle\Model\Complaint\ComplaintResolutionEnum;
use Shopsys\FrameworkBundle\Model\Complaint\Status\ComplaintStatus;
use Shopsys\FrameworkBundle\Model\Order\Item\OrderItem;
use Shopsys\FrameworkBundle\Model\Order\Order;
use Shopsys\FrameworkBundle\Model\PhonePrefix\PhoneData;
use Tests\FrontendApiBundle\Test\GraphQlWithLoginTestCase;

final class CreateComplaintQuantityTest extends GraphQlWithLoginTestCase
{
    /**
     * @inject
     */
    private EntityManagerInterface $entityManager;

    /**
     * @inject
     */
    private ComplaintFacade $complaintFacade;

    /**
     * @inject
     */
    private ComplaintDataFactory $complaintDataFactory;

    public function testRemainingQuantityCanBeClaimedAcrossMultipleComplaints(): void
    {
        $order = $this->getReference(OrderDataFixture::ORDER_PREFIX . 3, Order::class);
        $orderItem = $order->getProductItems()[0];
        $quantity = $orderItem->getQuantity();
        $this->assertGreaterThan(1, $quantity);

        $firstResponse = $this->createComplaintForQuantities($order, $orderItem, [1]);
        $firstComplaint = $this->getResponseDataForGraphQlType($firstResponse, 'CreateComplaint');
        $this->assertSame($quantity - 1, $firstComplaint['items'][0]['orderItem']['remainingComplaintQuantity']);

        $secondResponse = $this->createComplaintForQuantities($order, $orderItem, [$quantity - 1]);
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

    public function testResolvedMoneyReturnComplaintKeepsClaimedQuantity(): void
    {
        $order = $this->getReference(OrderDataFixture::ORDER_PREFIX . 2, Order::class);
        $orderItem = $order->getProductItems()[0];

        $response = $this->createComplaintForQuantities($order, $orderItem, [$orderItem->getQuantity()]);

        $this->assertUserError($response, 'invalid-quantity');
    }

    public function testResolvedFixComplaintReleasesClaimedQuantity(): void
    {
        $order = $this->getReference(OrderDataFixture::ORDER_PREFIX . 3, Order::class);
        $orderItem = $order->getProductItems()[0];

        $firstResponse = $this->createComplaintForQuantities($order, $orderItem, [$orderItem->getQuantity()]);
        $firstComplaint = $this->getResponseDataForGraphQlType($firstResponse, 'CreateComplaint');
        $this->resolveComplaint($firstComplaint['number']);

        $secondResponse = $this->createComplaintForQuantities($order, $orderItem, [$orderItem->getQuantity()]);
        $secondComplaint = $this->getResponseDataForGraphQlType($secondResponse, 'CreateComplaint');

        $this->assertSame(0, $secondComplaint['items'][0]['orderItem']['remainingComplaintQuantity']);
    }

    private function resolveComplaint(string $complaintNumber): void
    {
        $complaint = $this->entityManager->getRepository(Complaint::class)->findOneBy(['number' => $complaintNumber]);
        $complaintData = $this->complaintDataFactory->createFromComplaint($complaint);
        $complaintData->status = $this->getReference(ComplaintStatusDataFixture::COMPLAINT_STATUS_RESOLVED, ComplaintStatus::class);

        $this->complaintFacade->edit($complaint->getId(), $complaintData);
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
                    'resolution' => ComplaintResolutionEnum::FIX,
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
