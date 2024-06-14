<?php

declare(strict_types=1);

namespace Tests\FrameworkBundle\Unit\Model\Order\Status;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Shopsys\FrameworkBundle\Model\Order\Status\Exception\OrderStatusDeletionForbiddenException;
use Shopsys\FrameworkBundle\Model\Order\Status\OrderStatus;
use Shopsys\FrameworkBundle\Model\Order\Status\OrderStatusData;
use Shopsys\FrameworkBundle\Model\Order\Status\OrderStatusTypeEnum;

class OrderStatusTest extends TestCase
{
    public static function checkForDeleteProvider()
    {
        return [
            ['statusType' => OrderStatusTypeEnum::TYPE_NEW, 'expectedException' => OrderStatusDeletionForbiddenException::class],
            ['statusType' => OrderStatusTypeEnum::TYPE_IN_PROGRESS, 'expectedException' => null],
            ['statusType' => OrderStatusTypeEnum::TYPE_DONE, 'expectedException' => OrderStatusDeletionForbiddenException::class],
            ['statusType' => OrderStatusTypeEnum::TYPE_CANCELED, 'expectedException' => OrderStatusDeletionForbiddenException::class],
        ];
    }

    /**
     * @param mixed $statusType
     * @param mixed|null $expectedException
     */
    #[DataProvider('checkForDeleteProvider')]
    public function testCheckForDelete($statusType, $expectedException = null)
    {
        $orderStatusData = new OrderStatusData();
        $orderStatusData->name = ['en' => 'orderStatusName'];
        $orderStatus = new OrderStatus($orderStatusData, $statusType);

        if ($expectedException !== null) {
            $this->expectException($expectedException);
        }
        $orderStatus->checkForDelete();
    }
}
