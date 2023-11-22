<?php

declare(strict_types=1);

namespace Tests\FrameworkBundle\Unit\Model\Order\Status;

use PHPUnit\Framework\TestCase;
use Shopsys\FrameworkBundle\Model\Order\Status\Exception\OrderStatusDeletionForbiddenException;
use Shopsys\FrameworkBundle\Model\Order\Status\OrderStatus;
use Shopsys\FrameworkBundle\Model\Order\Status\OrderStatusData;

class OrderStatusTest extends TestCase
{
    /**
     * @return array<int, array<'expectedException'|'type', int|string|null>>
     */
    public function checkForDeleteProvider(): array
    {
        return [
            ['type' => OrderStatus::TYPE_NEW, 'expectedException' => OrderStatusDeletionForbiddenException::class],
            ['type' => OrderStatus::TYPE_IN_PROGRESS, 'expectedException' => null],
            ['type' => OrderStatus::TYPE_DONE, 'expectedException' => OrderStatusDeletionForbiddenException::class],
            ['type' => OrderStatus::TYPE_CANCELED, 'expectedException' => OrderStatusDeletionForbiddenException::class],
        ];
    }

    /**
     * @dataProvider checkForDeleteProvider
     * @param int $statusType
     * @param string|null $expectedException
     */
    public function testCheckForDelete(int $statusType, ?string $expectedException = null): void
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
