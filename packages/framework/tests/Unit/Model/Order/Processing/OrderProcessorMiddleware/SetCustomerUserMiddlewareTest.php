<?php

declare(strict_types=1);

namespace Tests\FrameworkBundle\Unit\Model\Order\Processing\OrderProcessorMiddleware;

use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserData;
use Shopsys\FrameworkBundle\Model\Order\Processing\OrderProcessorMiddleware\SetCustomerUserMiddleware;
use Tests\FrameworkBundle\Test\MiddlewareTestCase;

class SetCustomerUserMiddlewareTest extends MiddlewareTestCase
{
    public function testCustomerIsAdded(): void
    {
        $customerUserData = new CustomerUserData();
        $customerUserData->email = 'no-reply@example.com';
        $expectedCustomerUser = new CustomerUser($customerUserData);

        $orderProcessingData = $this->createOrderProcessingData();
        $orderProcessingData->orderInput->setCustomerUser($expectedCustomerUser);

        $setCustomerUserMiddleware = new SetCustomerUserMiddleware();

        $result = $setCustomerUserMiddleware->handle($orderProcessingData, $this->createOrderProcessingStack());
        $actualOrderData = $result->orderData;

        $this->assertSame($expectedCustomerUser, $actualOrderData->customerUser);
    }

    public function testCustomerUserIsIgnoredIfMissing(): void
    {
        $orderProcessingData = $this->createOrderProcessingData();

        $setCustomerUserMiddleware = new SetCustomerUserMiddleware();

        $result = $setCustomerUserMiddleware->handle($orderProcessingData, $this->createOrderProcessingStack());
        $actualOrderData = $result->orderData;

        $this->assertNull($actualOrderData->customerUser);
    }
}
