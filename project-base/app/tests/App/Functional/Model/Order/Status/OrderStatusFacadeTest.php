<?php

declare(strict_types=1);

namespace Tests\App\Functional\Model\Order\Status;

use App\DataFixtures\Demo\OrderDataFixture;
use App\DataFixtures\Demo\OrderStatusDataFixture;
use App\Model\Order\Order;
use App\Model\Order\Status\OrderStatus;
use Shopsys\FrameworkBundle\Model\Localization\Localization;
use Shopsys\FrameworkBundle\Model\Order\OrderDataFactory;
use Shopsys\FrameworkBundle\Model\Order\OrderFacade;
use Shopsys\FrameworkBundle\Model\Order\Status\OrderStatusData;
use Shopsys\FrameworkBundle\Model\Order\Status\OrderStatusFacade;
use Tests\App\Test\TransactionFunctionalTestCase;

class OrderStatusFacadeTest extends TransactionFunctionalTestCase
{
    /**
     * @inject
     */
    private OrderStatusFacade $orderStatusFacade;

    /**
     * @inject
     */
    private OrderFacade $orderFacade;

    /**
     * @inject
     */
    private OrderDataFactory $orderDataFactory;

    /**
     * @inject
     */
    private Localization $localization;

    public function testDeleteByIdAndReplace()
    {
        $orderStatusData = new OrderStatusData();
        $namesByLocale = [];

        foreach ($this->localization->getLocalesOfAllDomains() as $locale) {
            $namesByLocale[$locale] = 'name';
        }
        $orderStatusData->name = $namesByLocale;
        $orderStatusToDelete = $this->orderStatusFacade->create($orderStatusData);
        $orderStatusToReplaceWith = $this->getReference(OrderStatusDataFixture::ORDER_STATUS_NEW, OrderStatus::class);
        $order = $this->getReference(OrderDataFixture::ORDER_PREFIX . '1', Order::class);

        $orderData = $this->orderDataFactory->createFromOrder($order);
        $orderData->status = $orderStatusToDelete;
        $this->orderFacade->edit($order->getId(), $orderData);

        $this->orderStatusFacade->deleteById($orderStatusToDelete->getId(), $orderStatusToReplaceWith->getId());

        $this->em->refresh($order);

        $this->assertEquals($orderStatusToReplaceWith, $order->getStatus());
    }
}
