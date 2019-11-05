<?php

declare(strict_types=1);

namespace Tests\ShopBundle\Functional\Model\Order\Status;

use Shopsys\FrameworkBundle\Model\Order\Status\OrderStatusData;
use Shopsys\ShopBundle\DataFixtures\Demo\OrderDataFixture;
use Shopsys\ShopBundle\DataFixtures\Demo\OrderStatusDataFixture;
use Tests\ShopBundle\Test\TransactionFunctionalTestCase;

class OrderStatusFacadeTest extends TransactionFunctionalTestCase
{
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
        /** @var \Shopsys\ShopBundle\Model\Order\Order $order */
        $order = $this->getReference(OrderDataFixture::ORDER_PREFIX . '1');

        $orderData = $this->orderDataFactory->createFromOrder($order);
        $orderData->status = $orderStatusToDelete;
        $this->orderFacade->edit($order->getId(), $orderData);

        $this->orderStatusFacade->deleteById($orderStatusToDelete->getId(), $orderStatusToReplaceWith->getId());

        $em->refresh($order);

        $this->assertEquals($orderStatusToReplaceWith, $order->getStatus());
    }
}
