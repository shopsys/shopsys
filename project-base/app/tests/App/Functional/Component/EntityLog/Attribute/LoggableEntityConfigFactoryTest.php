<?php

declare(strict_types=1);

namespace Tests\App\Functional\Component\EntityLog\Attribute;

use App\Model\Order\Order;
use Shopsys\FrameworkBundle\Component\EntityLog\Attribute\Loggable;
use Shopsys\FrameworkBundle\Component\EntityLog\Attribute\LoggableEntityConfigFactory;
use Shopsys\FrameworkBundle\Model\Country\Country;
use Tests\App\Test\TransactionFunctionalTestCase;

class LoggableEntityConfigFactoryTest extends TransactionFunctionalTestCase
{
    /**
     * @inject
     */
    private LoggableEntityConfigFactory $loggableEntityConfigFactory;

    public function testGetLoggableSetupForOrderEntity(): void
    {
        $loggableSetup = $this->loggableEntityConfigFactory->getLoggableSetupByEntity(Order::class);

        $this->assertSame(Order::class, $loggableSetup->getEntityFullyQualifiedName());
        $this->assertSame('Order', $loggableSetup->getEntityName());
        $this->assertTrue($loggableSetup->isLoggable());
        $this->assertSame(Loggable::STRATEGY_INCLUDE_ALL, $loggableSetup->getStrategy());
        $this->assertTrue($loggableSetup->isPropertyLoggable('status'));
        $this->assertFalse($loggableSetup->isLocalized());
    }

    public function testGetLoggableSetupForLocalizedIdentification(): void
    {
        $loggableSetup = $this->loggableEntityConfigFactory->getLoggableSetupByEntity(Country::class);

        $this->assertSame('getName', $loggableSetup->getEntityReadableNameFunctionName());
        $this->assertTrue($loggableSetup->isLocalized());
    }
}
