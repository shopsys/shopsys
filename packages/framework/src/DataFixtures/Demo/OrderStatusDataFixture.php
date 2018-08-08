<?php

namespace Shopsys\FrameworkBundle\DataFixtures\Demo;

use Doctrine\Common\Persistence\ObjectManager;
use Shopsys\FrameworkBundle\Component\DataFixture\AbstractReferenceFixture;
use Shopsys\FrameworkBundle\Model\Order\Status\OrderStatusFacade;

class OrderStatusDataFixture extends AbstractReferenceFixture
{
    const ORDER_STATUS_NEW = 'order_status_new';
    const ORDER_STATUS_IN_PROGRESS = 'order_status_in_progress';
    const ORDER_STATUS_DONE = 'order_status_done';
    const ORDER_STATUS_CANCELED = 'order_status_canceled';

    /**
     * @var \Shopsys\FrameworkBundle\Model\Order\Status\OrderStatusFacade
     */
    private $orderStatusFacade;

    public function __construct(OrderStatusFacade $orderStatusFacade)
    {
        $this->orderStatusFacade = $orderStatusFacade;
    }

    public function load(ObjectManager $manager)
    {
        $this->createOrderStatusReference(1, self::ORDER_STATUS_NEW);
        $this->createOrderStatusReference(2, self::ORDER_STATUS_IN_PROGRESS);
        $this->createOrderStatusReference(3, self::ORDER_STATUS_DONE);
        $this->createOrderStatusReference(4, self::ORDER_STATUS_CANCELED);
    }

    /**
     * Order statuses are created (with specific ids) in database migration.
     *
     * @param int $orderStatusId
     * @param string $referenceName
     * @see \Shopsys\FrameworkBundle\Migrations\Version20180603135341
     */
    private function createOrderStatusReference(
        $orderStatusId,
        $referenceName
    ) {
        $orderStatus = $this->orderStatusFacade->getById($orderStatusId);
        $this->addReference($referenceName, $orderStatus);
    }
}
