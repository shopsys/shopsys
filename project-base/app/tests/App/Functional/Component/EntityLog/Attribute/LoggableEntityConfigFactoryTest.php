<?php

declare(strict_types=1);

namespace Tests\App\Functional\Component\EntityLog\Attribute;

use App\Model\Order\Order;
use Shopsys\FrameworkBundle\Component\EntityLog\Attribute\LoggableEntityConfigFactory;
use Tests\App\Test\TransactionFunctionalTestCase;

class LoggableEntityConfigFactoryTest extends TransactionFunctionalTestCase
{
    /**
     * @inject
     */
    private LoggableEntityConfigFactory $loggableEntityConfigFactory;

    public function testGetLoggableSetupForOrderEntity()
    {
        $loggableSetup = $this->loggableEntityConfigFactory->getLoggableSetupByEntity(Order::class);

        $this->assertSame(Order::class, $loggableSetup->getEntityFullyQualifiedName());
        $this->assertSame('Order', $loggableSetup->getEntityName());
        $this->assertTrue($loggableSetup->isLoggable());
    }
}
