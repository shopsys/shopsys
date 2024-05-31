<?php

declare(strict_types=1);

namespace Tests\App\Unit\Model\Order\Processing\OrderProcessorMiddleware;

use App\Model\Administrator\Administrator;
use App\Model\Administrator\AdministratorData;
use App\Model\Order\Processing\OrderProcessorMiddleware\SetOrderDataAsAdministratorMiddleware;
use App\Model\Security\LoginAsUserFacade;
use Tests\FrameworkBundle\Test\MiddlewareTestCase;

class SetOrderDataAsAdministratorMiddlewareTest extends MiddlewareTestCase
{
    /**
     * @dataProvider getAdministratorData
     * @param \App\Model\Administrator\Administrator|null $expectedAdministrator
     */
    public function testAdministratorDataIsAdded(?Administrator $expectedAdministrator): void
    {
        $orderProcessingData = $this->createOrderProcessingData();

        $loginAsUserFacade = $this->createMock(LoginAsUserFacade::class);
        $loginAsUserFacade->method('getCurrentAdministratorLoggedAsCustomer')->willReturn($expectedAdministrator);

        $setOrderDataAsAdministratorMiddleware = new SetOrderDataAsAdministratorMiddleware($loginAsUserFacade);

        $result = $setOrderDataAsAdministratorMiddleware->handle($orderProcessingData, $this->createOrderProcessingStack());
        $actualOrderData = $result->orderData;

        $this->assertSame($expectedAdministrator, $actualOrderData->createdAsAdministrator);
        $this->assertSame($expectedAdministrator?->getRealName(), $actualOrderData->createdAsAdministratorName);
    }

    /**
     * @return iterable
     */
    public function getAdministratorData(): iterable
    {
        $administratorData = new AdministratorData();
        $administratorData->realName = 'realName';

        yield 'administrator is set' => [
            'expectedAdministrator' => new Administrator($administratorData),
        ];

        yield 'administrator is not set' => [
            'expectedAdministrator' => null,
        ];
    }
}
