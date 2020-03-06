<?php

declare(strict_types=1);

namespace Tests\App\Functional\Model\Order\Status;

use App\DataFixtures\Demo\OrderDataFixture;
use App\DataFixtures\Demo\OrderStatusDataFixture;
use Shopsys\FrameworkBundle\Model\Order\Status\OrderStatusData;
use Tests\App\Test\TransactionFunctionalTestCase;
use Zalas\Injector\PHPUnit\Symfony\TestCase\SymfonyTestContainer;

class OrderStatusFacadeTest extends TransactionFunctionalTestCase
{
    use SymfonyTestContainer;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Order\Status\OrderStatusFacade
     * @inject
     */
    private $orderStatusFacade;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Order\OrderFacade
     * @inject
     */
    private $orderFacade;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Order\OrderDataFactoryInterface
     * @inject
     */
    private $orderDataFactory;

    public function testDeleteByIdAndReplace()
    {
        $em = $this->getEntityManager();

        $orderStatusData = new OrderStatusData();
        $orderStatusData->name = ['cs' => 'name'];
        $orderStatusToDelete = $this->orderStatusFacade->create($orderStatusData);
        /** @var \Shopsys\FrameworkBundle\Model\Order\Status\OrderStatus $orderStatusToReplaceWith */
        $orderStatusToReplaceWith = $this->getReference(OrderStatusDataFixture::ORDER_STATUS_NEW);
        /** @var \App\Model\Order\Order $order */
        $order = $this->getReference(OrderDataFixture::ORDER_PREFIX . '1');

        $orderData = $this->orderDataFactory->createFromOrder($order);
        $orderData->status = $orderStatusToDelete;
        $this->orderFacade->edit($order->getId(), $orderData);

        $this->orderStatusFacade->deleteById($orderStatusToDelete->getId(), $orderStatusToReplaceWith->getId());

        $em->refresh($order);

        $this->assertEquals($orderStatusToReplaceWith, $order->getStatus());
    }
}
