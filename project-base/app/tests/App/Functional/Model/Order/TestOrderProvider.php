<?php

declare(strict_types=1);

namespace Tests\App\Functional\Model\Order;

use App\Model\Order\OrderData;
use Override;
use Tests\FrameworkBundle\Test\Provider\TestOrderProvider as BaseTestOrderProvider;

/**
 * @method static \App\Model\Order\OrderData getTestOrderData()
 */
class TestOrderProvider extends BaseTestOrderProvider
{
    /**
     * @return \App\Model\Order\OrderData
     */
    #[Override]
    protected static function createOrderDataInstance(): OrderData
    {
        return new OrderData();
    }
}
